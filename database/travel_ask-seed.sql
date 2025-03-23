DROP SCHEMA IF EXISTS lbaw2412 CASCADE;
CREATE SCHEMA IF NOT EXISTS lbaw2412;
SET search_path TO lbaw2412;

DROP TABLE IF EXISTS vote_notification CASCADE;
DROP TABLE IF EXISTS answer_notification CASCADE;
DROP TABLE IF EXISTS image CASCADE;
DROP TABLE IF EXISTS event CASCADE;
DROP TABLE IF EXISTS follow_tag CASCADE;
DROP TABLE IF EXISTS question_tag CASCADE;
DROP TABLE IF EXISTS tag CASCADE;
DROP TABLE IF EXISTS vote CASCADE;
DROP TABLE IF EXISTS comment CASCADE;
DROP TABLE IF EXISTS answer CASCADE;
DROP TABLE IF EXISTS follow_question CASCADE;
DROP TABLE IF EXISTS question CASCADE;
DROP TABLE IF EXISTS post CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS city CASCADE;
DROP TABLE IF EXISTS country CASCADE;

DROP TYPE IF EXISTS vote_type CASCADE;
DROP TYPE IF EXISTS account_type CASCADE;


CREATE TYPE account_type AS ENUM ('Normal', 'Moderator', 'Administrator', 'Verified');
CREATE TYPE vote_type as ENUM ('Up', 'Down');

CREATE TABLE country (
    id SERIAL PRIMARY KEY,
    name TEXT NOT NULL UNIQUE,
    description TEXT NOT NULL
);
CREATE TABLE city (
    id SERIAL PRIMARY KEY,
    country_id INTEGER NOT NULL REFERENCES country(id) ON UPDATE CASCADE ON DELETE CASCADE,
    name TEXT NOT NULL,
    description TEXT NOT NULL
);

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username varchar(50) NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    name varchar(50) NOT NULL,
    password TEXT NOT NULL,
    bio varchar(1000),
    register_date TIMESTAMPTZ DEFAULT now() NOT NULL,
    photo TEXT,
    blocked BOOLEAN NOT NULL DEFAULT FALSE,
    deleted BOOLEAN NOT NULL DEFAULT FALSE,
    account account_type NOT NULL,
    travelling BOOLEAN NOT NULL DEFAULT FALSE,
    site TEXT,
    remember_token VARCHAR,
    notifications BOOLEAN NOT NULL DEFAULT TRUE 
);

CREATE TABLE post (
    id SERIAL PRIMARY KEY,
    date TIMESTAMPTZ DEFAULT now() NOT NULL,
    content TEXT NOT NULL,
    edit BOOLEAN NOT NULL DEFAULT FALSE,
    upvotes INTEGER NOT NULL DEFAULT 0,
    user_id INTEGER REFERENCES users(id) ON UPDATE CASCADE
);

CREATE TABLE question (
    post_id INTEGER PRIMARY KEY REFERENCES post(id) ON UPDATE CASCADE ON DELETE CASCADE,
    title varchar(100) NOT NULL UNIQUE,
    view_count INTEGER NOT NULL DEFAULT 0,
    country_id INTEGER REFERENCES country(id) ON UPDATE CASCADE,
    city_id INTEGER REFERENCES city(id) ON UPDATE CASCADE
);

CREATE TABLE follow_question (
    user_id INTEGER NOT NULL REFERENCES users(id) ON UPDATE CASCADE,
    question_id INTEGER NOT NULL REFERENCES question(post_id) ON UPDATE CASCADE ON DELETE CASCADE,
    PRIMARY KEY (user_id, question_id)
);

CREATE TABLE answer (
    post_id INTEGER PRIMARY KEY REFERENCES post(id) ON UPDATE CASCADE ON DELETE CASCADE,
    question_id INTEGER NOT NULL REFERENCES question(post_id) ON UPDATE CASCADE,
    correct BOOLEAN NOT NULL DEFAULT FALSE,
    deleted BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE comment (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON UPDATE CASCADE,
    post_id INTEGER NOT NULL REFERENCES post(id) ON UPDATE CASCADE ON DELETE CASCADE,
    date TIMESTAMPTZ DEFAULT now() NOT NULL,
    content varchar(1000) NOT NULL 
);

CREATE TABLE vote (
    user_id INTEGER NOT NULL REFERENCES users(id) ON UPDATE CASCADE,
    post_id INTEGER NOT NULL REFERENCES post(id) ON UPDATE CASCADE ON DELETE CASCADE,
    vote vote_type NOT NULL,
    date TIMESTAMPTZ DEFAULT now() NOT NULL,
    PRIMARY KEY (user_id, post_id)
);

CREATE TABLE tag (
    id SERIAL PRIMARY KEY,
    name TEXT NOT NULL UNIQUE
);

CREATE TABLE question_tag (
    question_id INTEGER NOT NULL REFERENCES question(post_id) ON UPDATE CASCADE ON DELETE CASCADE,
    tag_id INTEGER NOT NULL REFERENCES tag(id) ON UPDATE CASCADE ON DELETE CASCADE,
    PRIMARY KEY (question_id, tag_id)
);

CREATE TABLE follow_tag (
    user_id INTEGER NOT NULL REFERENCES users(id) ON UPDATE CASCADE,
    tag_id INTEGER NOT NULL REFERENCES tag(id) ON UPDATE CASCADE ON DELETE CASCADE,
    PRIMARY KEY (user_id, tag_id)
);

CREATE TABLE event (
    id SERIAL PRIMARY KEY,
    city_id INTEGER NOT NULL REFERENCES city(id) ON UPDATE CASCADE ON DELETE CASCADE,
    name TEXT NOT NULL,
    start_date TIMESTAMPTZ NOT NULL,
    end_date TIMESTAMPTZ NOT NULL,
    description TEXT NOT NULL,
    CHECK (end_date > start_date)
);

CREATE TABLE image (
    id SERIAL PRIMARY KEY,
    path TEXT NOT NULL UNIQUE, 
    name varchar(50) NOT NULL DEFAULT 'image',
    post_id INTEGER NOT NULL REFERENCES post(id) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE vote_notification (
    id SERIAL PRIMARY KEY,
    notified INTEGER NOT NULL REFERENCES users(id) ON UPDATE CASCADE,
    viewed BOOLEAN NOT NULL DEFAULT FALSE,
    date TIMESTAMPTZ NOT NULL DEFAULT now(),
    voter INTEGER NOT NULL,
    post_id INTEGER NOT NULL,
    FOREIGN KEY (voter, post_id) REFERENCES vote(user_id, post_id) ON UPDATE CASCADE ON DELETE CASCADE,
    UNIQUE (voter, post_id)
);

CREATE TABLE answer_notification (
    id SERIAL PRIMARY KEY,
    notified INTEGER NOT NULL REFERENCES users(id) ON UPDATE CASCADE,
    viewed BOOLEAN NOT NULL DEFAULT FALSE,
    date TIMESTAMPTZ NOT NULL DEFAULT now(),
    answer_id INTEGER NOT NULL REFERENCES answer(post_id) ON UPDATE CASCADE ON DELETE CASCADE
);



CREATE TABLE password_resets (
    email TEXT NOT NULL,
    token VARCHAR(50) NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    PRIMARY KEY (email)
);





CREATE OR REPLACE FUNCTION check_if_can_vote() 
RETURNS TRIGGER AS $$
BEGIN
    IF EXISTS (
        SELECT id from post 
        where id = NEW.post_id AND user_id = NEW.user_id
    ) THEN
        RAISE EXCEPTION 'An author cannot vote on his own post!';
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE TRIGGER check_if_can_vote_trigger
BEFORE INSERT ON vote 
FOR EACH ROW
EXECUTE PROCEDURE check_if_can_vote();

CREATE OR REPLACE FUNCTION set_edit_indication() 
RETURNS TRIGGER AS $$
BEGIN
    IF OLD.content != NEW.content THEN
        NEW.edit = TRUE;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE TRIGGER set_edit_indication_trigger
BEFORE UPDATE ON post
FOR EACH ROW
EXECUTE PROCEDURE set_edit_indication();


CREATE OR REPLACE FUNCTION anonymise_user_data()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE users 
    SET username = OLD.id::TEXT,  
        name = OLD.id::TEXT,       
        email = OLD.id::TEXT,      
        password = OLD.id::TEXT,   
        bio = NULL,                
        photo = NULL,              
        deleted = TRUE,            
        site = NULL                
    WHERE id = OLD.id;

    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE TRIGGER anonymise_user_data_trigger
BEFORE DELETE ON users  
FOR EACH ROW
EXECUTE PROCEDURE anonymise_user_data();

CREATE OR REPLACE FUNCTION send_vote_notification() 
RETURNS TRIGGER AS $$
DECLARE
notified INTEGER;
BEGIN
    SELECT post.user_id INTO notified
    FROM post 
    JOIN vote ON vote.post_id = post.id
    WHERE vote.post_id = NEW.post_id;

   INSERT INTO vote_notification (notified, voter, post_id) VALUES (notified, NEW.user_id, NEW.post_id);

   RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE TRIGGER send_vote_notification_trigger
AFTER INSERT ON vote
FOR EACH ROW
EXECUTE PROCEDURE send_vote_notification();

CREATE OR REPLACE FUNCTION follow_question_author() 
RETURNS TRIGGER AS $$
DECLARE
author INTEGER;
BEGIN
    SELECT post.user_id INTO author
    FROM post 
    JOIN question ON question.post_id = post.id
    WHERE question.post_id = NEW.post_id;

   INSERT INTO follow_question (user_id, question_id) VALUES (author, NEW.post_id);

   RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE TRIGGER follow_question_author_trigger
AFTER INSERT ON question
FOR EACH ROW
EXECUTE PROCEDURE follow_question_author();

CREATE OR REPLACE FUNCTION send_answer_notification() 
RETURNS TRIGGER AS $$
DECLARE
follower_id INTEGER;
BEGIN
    FOR follower_id IN
        SELECT user_id FROM follow_question
        WHERE question_id = NEW.question_id
    LOOP
        INSERT INTO answer_notification (notified, answer_id) 
        VALUES (follower_id, NEW.post_id);
    END LOOP;

   RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE TRIGGER send_answer_notification_trigger
AFTER INSERT ON answer
FOR EACH ROW
EXECUTE PROCEDURE send_answer_notification();

CREATE OR REPLACE FUNCTION check_can_remove_question()
RETURNS TRIGGER AS $$
BEGIN
   IF EXISTS (
       SELECT * from answer
       where answer.question_id = OLD.post_id
   ) OR EXISTS (
       SELECT * from comment
       where comment.post_id = OLD.post_id
   ) THEN
       RAISE EXCEPTION 'cannot delete question with answers or comments!';
   END IF;
   RETURN OLD;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE TRIGGER check_can_remove_question_trigger
BEFORE DELETE ON question
FOR EACH ROW
EXECUTE PROCEDURE check_can_remove_question();

CREATE OR REPLACE FUNCTION delete_question_post()
RETURNS TRIGGER AS $$
BEGIN
    DELETE FROM post WHERE id=OLD.post_id;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE TRIGGER delete_question_post_trigger
AFTER DELETE ON question  
FOR EACH ROW
EXECUTE PROCEDURE delete_question_post();

CREATE OR REPLACE FUNCTION delete_answer()
RETURNS TRIGGER AS $$
BEGIN
    IF EXISTS (SELECT * FROM comment WHERE post_id = OLD.post_id)
    THEN
        UPDATE answer
        SET deleted = TRUE
        WHERE post_id = OLD.post_id;
        UPDATE post
        SET content = ''
        WHERE post.id = OLD.post_id;
        RETURN NULL;
    END IF;
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE TRIGGER delete_answer_trigger
BEFORE DELETE ON answer  
FOR EACH ROW
EXECUTE PROCEDURE delete_answer();

CREATE OR REPLACE FUNCTION delete_answer_post()
RETURNS TRIGGER AS $$
BEGIN
    DELETE FROM post WHERE id=OLD.post_id;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE TRIGGER delete_answer_post_trigger
AFTER DELETE ON answer  
FOR EACH ROW
EXECUTE PROCEDURE delete_answer_post();

CREATE OR REPLACE FUNCTION update_question_fts_index(id_question INT)
RETURNS VOID AS $$
DECLARE
    v_title TEXT;
    v_content TEXT;
    v_agg_content TEXT;
BEGIN
    SELECT title INTO v_title FROM question WHERE question.post_id = id_question;

    SELECT content INTO v_content FROM post WHERE post.id = (SELECT post_id FROM question WHERE question.post_id = id_question);
    
    SELECT STRING_AGG(post.content, ' ') INTO v_agg_content
    FROM post
    JOIN answer ON post.id = answer.post_id
    WHERE answer.question_id = id_question;

    UPDATE question
    SET tsvectors = (
        setweight(to_tsvector('english', v_title), 'A') ||
        setweight(to_tsvector('english', v_content), 'B') || 
        setweight(to_tsvector('english', COALESCE(v_agg_content, '')), 'C') 
    )
    WHERE post_id = id_question; 

END;
$$ LANGUAGE plpgsql;

ALTER TABLE question ADD COLUMN tsvectors TSVECTOR;

CREATE OR REPLACE FUNCTION question_search_update() RETURNS TRIGGER AS $$
BEGIN
    IF (TG_OP = 'INSERT' or (TG_OP = 'UPDATE' and (NEW.title != (SELECT title from question where post_id = NEW.post_id))))
    THEN 
        NEW.tsvectors := (
            setweight(to_tsvector('english', NEW.title),'A') ||
            setweight(to_tsvector('english', (SELECT content FROM post WHERE id = NEW.post_id)), 'B') ||
            setweight(to_tsvector('english', COALESCE((SELECT STRING_AGG(post.content, ' ') FROM post 
                                                        JOIN answer ON post.id = answer.post_id 
                                                        WHERE answer.question_id = NEW.post_id), '')), 'C') 
        );
    END IF;
RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE TRIGGER question_search_update_trigger
BEFORE INSERT OR UPDATE ON question
FOR EACH ROW
EXECUTE PROCEDURE question_search_update();

CREATE OR REPLACE FUNCTION post_search_update() RETURNS TRIGGER AS $$
DECLARE
questionId INTEGER;
BEGIN
    IF EXISTS (
        SELECT * FROM question WHERE post_id=NEW.id
    ) THEN
        PERFORM update_question_fts_index(NEW.id);
    ELSE 
        IF EXISTS (SELECT * FROM answer WHERE post_id=NEW.id) THEN
            SELECT question_id INTO questionId FROM answer WHERE post_id=NEW.id;
            PERFORM update_question_fts_index(questionId);
        END IF;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE TRIGGER post_search_update_trigger
AFTER UPDATE ON post
FOR EACH ROW
EXECUTE PROCEDURE post_search_update();

CREATE OR REPLACE FUNCTION post_search_update_answer() RETURNS TRIGGER AS $$
BEGIN
        PERFORM update_question_fts_index(NEW.question_id);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE TRIGGER post_search_update_answer_trigger
AFTER INSERT ON answer
FOR EACH ROW
EXECUTE PROCEDURE post_search_update_answer();

CREATE INDEX search_idx ON question USING GIST(tsvectors);

CREATE INDEX IDX01 ON answer USING btree (question_id);
CLUSTER answer USING IDX01;

CREATE INDEX IDX02 ON vote_notification USING hash (notified);

CREATE INDEX IDX03 ON comment USING btree (post_id);
CLUSTER comment USING IDX03;

CREATE OR REPLACE FUNCTION update_upvote_count()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.vote = 'Up' THEN
        UPDATE post
        SET upvotes = (SELECT COUNT(*) FROM vote WHERE post_id = NEW.post_id AND vote = 'Up')
        WHERE id = NEW.post_id;
    END IF;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER upvote_count_trigger
AFTER INSERT OR DELETE ON vote
FOR EACH ROW
EXECUTE FUNCTION update_upvote_count();


/************** POPULATE *******************/

BEGIN;

INSERT INTO country (id, name, description) VALUES 
(1, 'United States', 'A country known for diverse cultures and landscapes'),
(2, 'Italy', 'Known for its art, architecture, and rich history'),
(3, 'Japan', 'A blend of ancient traditions and modern technology'),
(4, 'Australia', 'Famous for its beaches, coral reefs, and unique wildlife'),
(5, 'Brazil', 'Known for its vibrant culture and Amazon rainforest'),
(6, 'Canada', 'A country in North America'),
(7, 'United Kingdom', 'A country in Europe'),
(8, 'France', 'Famous for its cuisine, fashion, and art'),
(9, 'Germany', 'Known for its history, castles, and beer'),
(10, 'Mexico', 'Famous for its food, culture, and historical landmarks'),
(11, 'South Korea', 'A country blending modern technology with traditional culture'),
(12, 'India', 'Rich in culture, history, and cuisine');

INSERT INTO city (id, country_id, name, description) VALUES 
(1, 1, 'New York', 'Known for its skyline and vibrant city life'),
(2, 1, 'San Francisco', 'Home to the Golden Gate Bridge and Silicon Valley'),
(3, 1, 'Los Angeles', 'Famous for Hollywood and the entertainment industry'),
(4, 2, 'Rome', 'Historic city with ancient architecture like the Colosseum'),
(5, 2, 'Venice', 'Known for its canals and beautiful architecture'),
(6, 2, 'Florence', 'Home to art treasures such as Michelangelo''s David'),
(7, 3, 'Tokyo', 'Capital of Japan with modern and traditional attractions'),
(8, 3, 'Kyoto', 'A city famous for its temples, gardens, and historic sites'),
(9, 4, 'Sydney', 'Popular for the Sydney Opera House and beautiful beaches'),
(10, 4, 'Melbourne', 'Known for its vibrant arts scene and laneways'),
(11, 5, 'Rio de Janeiro', 'Known for its carnival, beaches, and Christ the Redeemer statue'),
(12, 5, 'São Paulo', 'Brazil''s largest city, known for its culture and food scene'),
(13, 6, 'Toronto', 'Canada''s largest city, known for multiculturalism and the CN Tower'),
(14, 6, 'Vancouver', 'Known for its stunning natural beauty and proximity to mountains and ocean'),
(15, 7, 'London', 'Known for landmarks like Big Ben, Buckingham Palace, and the British Museum'),
(16, 7, 'Edinburgh', 'Famous for its historic castle and the annual arts festival'),
(17, 8, 'Paris', 'Known for its iconic Eiffel Tower, museums, and cafés'),
(18, 8, 'Nice', 'Located on the French Riviera, famous for its beaches and Mediterranean climate'),
(19, 9, 'Berlin', 'Known for its history, the Berlin Wall, and vibrant culture'),
(20, 9, 'Munich', 'Famous for Oktoberfest, beer gardens, and Bavarian culture'),
(21, 10, 'Mexico City', 'Known for its rich history, cuisine, and landmarks like Teotihuacan'),
(22, 10, 'Cancún', 'Famous for its beautiful beaches and resorts'),
(23, 11, 'Seoul', 'Capital of South Korea, known for its technology, palaces, and K-pop'),
(24, 11, 'Busan', 'A coastal city famous for its beaches, seafood, and cultural festivals'),
(25, 12, 'Delhi', 'A city rich in history and culture, home to famous landmarks like the Red Fort'),
(26, 12, 'Mumbai', 'Known for Bollywood, its vibrant culture, and the Gateway of India');

INSERT INTO users (id, username, email, name, password, bio, account, site) VALUES
(1, 'marco_polo', 'marco@example.com', 'Marco Polo', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Just a Normal Dude who loves exploring!', 'Normal', NULL),
(2, 'alice_travels', 'alice@example.com', 'Alice Wonder', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Travel blogger from NYC', 'Verified', 'www.alicetravels.com'), -- password 1234
(3, 'admin_globeguru', 'admin@example.com', 'GlobeGuru Admin', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Administrator for Travel&Ask', 'Administrator', NULL),
(4, 'mod_peter', 'peter@example.com', 'Peter Moderator', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Responsible for managing travel content', 'Moderator', NULL),
(5, 'explorer_jane', 'jane@example.com', 'Jane Explorer', 'explorerpass', 'Adventure enthusiast exploring new cultures', 'Normal', NULL),
(6, 'londoner', 'london@gmail.com', 'Londoner', 'londonpass', 'Londoner', 'Normal', NULL),
(7, 'world_wanderer', 'wanderer@example.com', 'Wanda Traveler', 'wanderpass', 'Passionate about exploring the world and documenting my journey', 'Normal', NULL),
(8, 'chef_on_the_road', 'chef_road@example.com', 'Gordon Ramsay', 'chefpass123', 'Traveling chef exploring the world''s cuisines', 'Verified', 'www.chefroad.com'),
(9, 'urban_nomad', 'nomad@example.com', 'Sophia Urban', 'nomadpass', 'Living in different cities and capturing the urban life', 'Normal', NULL),
(10, 'backpacker_bob', 'bob@example.com', 'Bob Backpacker', 'backpackpass', 'Backpacking around the world, one country at a time', 'Normal', NULL),
(11, 'luxury_lara', 'lara@example.com', 'Lara Luxe', 'luxurypass', 'Luxury travel and five-star experiences', 'Verified', 'www.luxurylara.com'),
(12, 'traveling_tina', 'tina@example.com', 'Tina Traveler', 'tinapassword', 'Solo traveler with a love for adventure', 'Normal', NULL),
(13, 'globetrotter_gerald', 'gerald@example.com', 'Gerald Globetrotter', 'geraldpass', 'Exploring every corner of the earth, from jungles to deserts', 'Normal', NULL),
(14, 'asia_adventurer', 'asia@adventure.com', 'Liam Asia', 'asiapassword', 'Exploring the wonders of Asia and its diverse cultures', 'Normal', NULL),
(15, 'caribbean_queen', 'caribbean@example.com', 'Carla Caribbean', 'caribbeanpass', 'Enjoying the beaches and vibrant cultures of the Caribbean', 'Normal', NULL),
(16, 'desert_dan', 'dan@desert.com', 'Daniel Desert', 'desertpass', 'Traveling through the world''s most beautiful deserts', 'Normal', NULL),
(17, 'nature_nina', 'nina@nature.com', 'Nina Nature', 'naturepass', 'Passionate about eco-tourism and preserving nature', 'Verified', 'www.naturenina.com'),
(18, 'mountain_mike', 'mike@mountains.com', 'Mike Mountain', 'mountainpass', 'Climbing the highest peaks and enjoying scenic landscapes', 'Normal', NULL),
(19, 'history_hunter', 'hunter@example.com', 'Henry Historian', 'historianpass', 'Exploring the historical landmarks of the world', 'Normal', NULL),
(20, 'explorer_ella', 'ella@explorer.com', 'Ella Explorer', 'explorerella123', 'Exploring remote areas and documenting lost cultures', 'Normal', NULL);

SELECT setval('users_id_seq', (SELECT MAX(id) FROM users));


INSERT INTO post (id, content, user_id) VALUES 
(1, '<p>What are the safest neighborhoods to stay in NYC?</p>', 1),
(2, '<p>Can someone recommend authentic Italian restaurants in Rome?</p>', 2),
(3, '<p>What are some of the best places to visit at the moment?</p>', 3),
(4, '<p>How to manage jet lag after a long flight?</p>', 4),
(5, '<p>Looking for travel companions for a road trip through Australia</p>', 5),
(6, '<p>I''m planning a trip to London and looking for the best places to visit. Could anyone suggest iconic landmarks and hidden gems in the city?</p>', 7),
(7, '<p>I''m visiting London soon and would love to explore some lesser-known places. What are some hidden gems or off-the-beaten-path locations?</p>', 10),
(8, '<p>I''m going to Venice during the off-season and I want to avoid the tourist crowds. What are the best things to do in the city when it''s less crowded?</p>', 8),
(9, '<p>I''m heading to the Amazon Rainforest and want to make sure I''m prepared. Can anyone share tips for exploring safely and what to expect?</p>', 9),
(10, '<p>I''m planning a hiking trip in Vancouver. What are some of the best trails to explore? I''m looking for both challenging and scenic options.</p>', 15),
(11, '<p>I''m visiting Tokyo soon and want to dive into the cultural side of the city. What are the top cultural attractions and experiences I shouldn''t miss?</p>', 13),
(12, '<p>I''m considering a solo trip to Rio de Janeiro. Is it safe to explore the city alone? Any tips on staying safe while visiting popular spots?</p>', 11),
(13, '<p>I''m planning a family trip to Toronto. Can anyone recommend activities that are fun for kids and adults alike? What are the top family-friendly attractions?</p>', 12),
(14, '<p>I''m in Sydney and looking for great day trips outside the city. Any recommendations for scenic drives or nearby spots worth visiting?</p>', 14),
(15, '<p>I''m traveling to Kyoto and want to experience traditional Japanese culture. What are the best cultural experiences I should have while I''m there?</p>', 18),
(16, '<p>I''m visiting Paris and want to see the must-visit landmarks. What are the top attractions in the city, from the Eiffel Tower to museums and historical sites?</p>', 17),
(17, '<p>I''m on a budget in London and wondering how I can save money while still enjoying the top attractions. Any tips for budget-friendly travel in the city?</p>', 19),
(18, '<p>I''m planning a beach vacation to Cancún and want to know the best time to visit for good weather and fewer crowds. When is the ideal time to go?</p>', 20),
(19, '<p>I''m heading to Berlin and would love to learn about its rich history. What are the must-see historical sites and museums for history buffs?</p>', 6),
(20, '<p>I''m looking for outdoor adventures in Vancouver. What are the best places for hiking, biking, and exploring nature in the area?</p>', 16),
(21, '<p>Some of the best places to visit in London include the British Museum, Buckingham Palace, and the Tower of London. For hidden gems, consider visiting places like Leadenhall Market, Hampstead Heath, or the Kyoto Garden in Holland Park.</p>', 20),
(22, '<p>London has many hidden gems! Try exploring the neighborhoods of Shoreditch and Hackney for street art, or take a walk along the Regent''s Canal for scenic views. For something truly unique, visit the Leighton House Museum or the Victoria Miro Gallery.</p>', 19),
(23, '<p>When visiting Venice in the off-season, take a gondola ride through the quieter canals, visit the Rialto Market, and explore hidden squares like Campo Santa Margherita. You can also tour the quieter islands of Murano and Burano, which are less crowded but equally beautiful.</p>', 18),
(24, '<p>To safely explore the Amazon Rainforest, make sure to travel with a certified guide or tour operator. Be prepared with insect repellent, appropriate clothing, and vaccinations. Always stay in designated eco-lodges and respect local wildlife and indigenous communities.</p>', 17),
(25, '<p>Vancouver has some amazing hikes! Try the Grouse Grind for a challenging climb with spectacular views. For something more scenic, consider the trails in Stanley Park or the Garibaldi Provincial Park for alpine beauty and lakes. Check out Lighthouse Park for coastal views.</p>', 16),
(26, '<p>Tokyo offers some incredible cultural attractions, like the Senso-ji Temple, Meiji Shrine, and the Edo-Tokyo Museum. Don''t miss experiencing a traditional tea ceremony or visiting the National Museum to learn about Japan''s history and cultural heritage.</p>', 15),
(27, '<p>Rio de Janeiro is generally safe for solo travelers if you take precautions. Stick to well-populated areas, avoid walking alone at night, and be cautious in neighborhoods with higher crime rates. Visit popular spots like Copacabana Beach and Sugarloaf Mountain but always stay aware of your surroundings.</p>', 14),
(28, '<p>In Toronto, you can visit the Royal Ontario Museum for a family-friendly experience, or check out the Ontario Science Centre. The Toronto Islands offer biking and picnicking spots, while the Ripley''s Aquarium of Canada and High Park Zoo are great for kids.</p>', 13),
(29, '<p>Sydney has some fantastic day trips! Visit the Blue Mountains for hiking, or go whale watching in season. The Hunter Valley offers wine tasting tours, and if you''re into beaches, Bondi to Coogee coastal walk is a must for stunning coastal views.</p>', 12),
(30, '<p>To experience traditional culture in Kyoto, visit Kinkaku-ji (Golden Pavilion), Fushimi Inari Shrine with its iconic red torii gates, and the Gion district to see traditional tea houses and geishas. You can also participate in a traditional Japanese tea ceremony.</p>', 11),
(31, '<p>In Paris, must-see landmarks include the Eiffel Tower, the Louvre, Notre-Dame Cathedral, and Montmartre. Explore the Champs-Élysées, visit the Musée d''Orsay for Impressionist art, and take a Seine River cruise for views of iconic bridges and landmarks.</p>', 10),
(32, '<p>To save money in London, consider using an Oyster card for transportation instead of single tickets. Many museums, like the British Museum and Tate Modern, are free. Explore the city by walking or cycling, and avoid tourist traps in places like Covent Garden.</p>', 9),
(33, '<p>The best time to visit Cancún is between November and April when the weather is warm and dry, but there are fewer crowds than in the winter holidays. Avoid peak holiday seasons like Christmas or spring break for a more relaxed experience.</p>', 8),
(34, '<p>In Berlin, must-see historical sites include the Berlin Wall Memorial, Brandenburg Gate, and Checkpoint Charlie. Don''t miss the Memorial to the Murdered Jews of Europe and the Deutsches Historisches Museum for an in-depth look at Germany''s past.</p>', 7),
(35, '<p>For outdoor adventures in Vancouver, head to Grouse Mountain for hiking and skiing in winter. Stanley Park offers biking and walking paths, while you can also explore the stunning Sea-to-Sky Highway for coastal views and hikes in Garibaldi Provincial Park.</p>', 6);

SELECT setval('post_id_seq', (SELECT MAX(id) FROM post));

INSERT INTO question (post_id, title, view_count, country_id, city_id) VALUES 
(1, 'Safe Neighborhoods in NYC', 200, 1, 1),
(2, 'Authentic Dining in Rome', 150, 2, 4),
(3, 'Best places', 150, 2, 4),
(4, 'Managing Jet Lag Tips', 100, 1, NULL),
(5, 'Road Trip in Australia: Tips Needed', 80, 4, NULL),
(6, 'What are the best places to visit in London?', 50, 7, 15),
(7, 'Exploring London''s Hidden Gems', 75, 7, 15),
(8, 'What to Do in Venice in the Off-Season', 60, 2, 5),
(9, 'How to Experience the Amazon Rainforest Safely', 110, 5, NULL),
(10, 'Best Hikes Around Vancouver', 85, 6, NULL),
(11, 'What Are the Top Cultural Attractions in Tokyo?', 150, 3, 7),
(12, 'Is it Safe to Visit Rio de Janeiro as a Solo Traveler?', 140, 5, 11),
(13, 'Top Family-Friendly Activities in Toronto', 130, 6, 13),
(14, 'What Are the Best Day Trips from Sydney?', 95, 4, 9),
(15, 'How to Experience Traditional Culture in Kyoto', 100, 3, 8),
(16, 'Must-See Landmarks in Paris', 200, 8, 17),
(17, 'How to Save Money in London as a Tourist', 60, 7, 15),
(18, 'Best Time to Visit the Beaches of Cancún', 120, 10, 22),
(19, 'Things to Do in Berlin for History Buffs', 110, 9, 19),
(20, 'What Are the Best Outdoor Adventures in Vancouver?', 140, 6, 14);


INSERT INTO follow_question (user_id, question_id) VALUES 
(2, 1), 
(4, 2), 
(1, 4),
(3, 4);

INSERT INTO answer (post_id, question_id, correct, deleted) VALUES 
(21, 6, FALSE, FALSE),
(22, 7, FALSE, FALSE),
(23, 8, TRUE, FALSE),
(24, 9, FALSE, FALSE),
(25, 10, FALSE, FALSE),
(26, 11, FALSE, FALSE),
(27, 12, TRUE, FALSE),
(28, 13, FALSE, FALSE),
(29, 14, FALSE, FALSE),
(30, 15, FALSE, FALSE),
(31, 16, FALSE, FALSE),
(32, 17, TRUE, FALSE),
(33, 18, FALSE, FALSE),
(34, 19, TRUE, FALSE),
(35, 20, FALSE, FALSE);

INSERT INTO comment (user_id, post_id, content) VALUES 
(5, 1, 'I stayed in Brooklyn and felt very safe, definitely recommend it!'),
(4, 2, 'Trattoria in Rome has amazing pasta, highly recommended!'),
(2, 3, 'Looking forward to the new features, keep up the good work!'),
(1, 4, 'Drinking lots of water and adjusting sleep schedule helps with jet lag.'),
(3, 5, 'Sounds like a fun trip! Be sure to stop by the Great Barrier Reef.');

INSERT INTO vote (user_id, post_id, vote, date) VALUES 
(5, 1, 'Up', NOW()),
(3, 2, 'Up', NOW()),
(1, 3, 'Down', NOW()),
(2, 4, 'Up', NOW()),
(4, 5, 'Down', NOW());

INSERT INTO tag (id, name) VALUES 
(1, 'safety'), 
(2, 'food'), 
(3, 'tips'), 
(4, 'events'), 
(5, 'adventure');

SELECT setval('tag_id_seq', (SELECT MAX(id) FROM tag));


INSERT INTO question_tag (question_id, tag_id) VALUES 
(1, 1), 
(2, 2), 
(4, 3),  
(5, 5);  

INSERT INTO follow_tag (user_id, tag_id) VALUES 
(1, 1), 
(2, 2), 
(4, 3), 
(3, 1), 
(5, 5);  

INSERT INTO event (city_id, name, start_date, end_date, description) VALUES 
(1, 'NYC Marathon', '2025-11-10 08:00', '2025-11-11 22:00', 'Annual marathon through NYC’s five boroughs'),
(3, 'Rome Food Festival', '2026-11-10 08:00', '2026-11-12 23:00', 'Celebrate Italian food and wine in the heart of Rome'),
(4, 'Tokyo Cherry Blossom Festival', '2025-03-25', '2025-03-28', 'Annual cherry blossom viewing in Tokyo'),
(5, 'Sydney Opera House Concert', '2024-12-15 20:00', '2024-12-15 24:00', 'Exclusive concert event at the Sydney Opera House');

INSERT INTO image (path, name, post_id) VALUES 
('/images/nyc_neighborhoods.jpg', 'NYC Safe Neighborhoods', 1),
('/images/rome_dining.jpg', 'Authentic Italian Dining', 2),
('/images/jetlag_tips.jpg', 'Jet Lag Management', 4),
('/images/australia_roadtrip.jpg', 'Australian Road Trip', 5);

COMMIT;

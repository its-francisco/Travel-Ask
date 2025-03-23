## Project
The Travel&Ask project is a Q&A platform aimed at facilitating global travel knowledge sharing. It provides users with the ability to ask questions, share experiences, and access tailored information about destinations, safety, accommodations, and more.
With our product, users can post and browse questions/answers with full-text, multiple attributes and filtered search. They can order results (most-viewed, most-voted, publication date).
Users can engage with the community by voting on questions and answers, commenting and answering.
We provide a personalized user experience via personal feeds and notifications for followed tags and questions, profiles with editable information and verified accounts for credible contributions.
There are moderation tools for managing inappropriate content and accounts and administrators have the ability to block/unblock users and manage tags efficiently.
We offer innovative travel features such as events calendars highlighting cultural and local events, "Currently Traveling" feature for urgent assistance and a clickable map with common destinations.
The platform promotes community-driven travel advice with innovative tools for personalization and real-time assistance.

## Installation
Link to the final release:

#### Docker command
To run the latest version of our website, it is first required to login to GitLab's Container Registry (using FEUP VPN/network):

bash
docker login gitlab.up.pt:5050


After this, the image can be run with:

bash
docker run -d --name lbaw2412 -p 8000:80 gitlab.up.pt:5050/lbaw/lbaw2425/lbaw2412

The application will be available at http://localhost:8000

## Usage

Administration URL - [http://localhost:8000/admin](http://localhost:8000/admin)

| Username (email) | Password |
| -------- | -------- |
| admin@example.com    | 1234 |

#### 2.2. User Credentials

| Type          | Username (email)  | Password |
| ------------- | --------- | -------- |
| basic account | marco@example.com | 1234 |
| verified | alice@example.com    | 1234 |
| moderator | peter@example.com | 1234 |
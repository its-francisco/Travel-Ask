Due to seeding of the db, it may occur error when registering users.

You need to run:
SELECT setval(pg_get_serial_sequence('users', 'id'), coalesce(max(id)+1, 1), false) FROM users;

After this, it will change the serial sequence number



SELECT setval(pg_get_serial_sequence('post', 'id'), coalesce(max(id)+1, 1), false) FROM post;


added: php artisan storage:link 
so that photos are accessible!


it's also important that the following lines are added to .env 

MAIL_FROM_ADDRESS=travelask@lbawfeup.pt
MAIL_FROM_NAME=TravelAsk 
MAIL_MAILER=
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=


# for notifications:

BROADCAST_DRIVER=pusher
PUSHER_APP_ID=1906260
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=eu
PUSH_NOTIFICATIONS_BEARER=


to send in-app notifications: event(new Notification('hello world', 1, 'vote', 1)); (check App/Events/Notification for more info)
to send push notificatiosn: PushNotifications::sendPushNotification("hello", "this is travelask"); (check App/Controllers/PushNotifications for more info))

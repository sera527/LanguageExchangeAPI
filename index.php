<?php
/**
 * Step 1: Require the Slim Framework
 *
 * If you are not using Composer, you need to require the
 * Slim Framework and register its PSR-0 autoloader.
 *
 * If you are using Composer, you can skip this step.
 */
require 'Slim/Slim.php';
require __DIR__ . '/vendor/autoload.php';
\Slim\Slim::registerAutoloader();

/**
 * Step 2: Instantiate a Slim application
 *
 * This example instantiates a Slim application using
 * its default settings. However, you will usually configure
 * your Slim application now by passing an associative array
 * of setting names and values into the application constructor.
 */
$app = new \Slim\Slim();

/**
 * Step 3: Define the Slim application routes
 *
 * Here we define several Slim application routes that respond
 * to appropriate HTTP request methods. In this example, the second
 * argument for `Slim::get`, `Slim::post`, `Slim::put`, `Slim::patch`, and `Slim::delete`
 * is an anonymous function.
 */

// GET route
$app->get(
    '/dialogs',
    function () use ($app) {
        $social_id = $app->request()->get('social_id');

        $a = new db_connect();
        $b = $a->connect();

        $row1 = $b->query("

        ");

        $result = array(
            'dialog_id' => $social_id,
            'friend_first_name' => $a,
            'friend_last_name' => $a,
            'friend_avatar_url' => $a,
            'date_of_last_message' => $a,
            'answered' => true
        );
        $app->response['Content-Type'] = 'application/json';
        echo json_encode( $result );
});

$app->get(
    '/messages',
    function () use ($app) {
        echo "ololo";
});

$app->get(
    '/questions/all',
    function () use ($app) {
        $social_id = $app->request()->get('social_id');
        $question_language = $app->request()->get('question_language');
        $speaks_languages = $app->request()->get('speaks_languages');
        $speaks_languages = explode(",", $speaks_languages);
        $country = $app->request()->get('country');

        $languages ="";
        $t=0;
        foreach($speaks_languages as $l){
            $languages.= "learning_lang = '".$l."'";
            $t++;
            if($t<count($speaks_languages)){
                $languages.= " OR ";
            }
        }
        $a = new db_connect();
        $b = $a->connect();
        $row1[0] = 0;
        if($country == NULL){
            $row1 = $b->query("
                SELECT q.question_id, u.first_name, u.last_name, u.avatar_url, q.audio_url, u.country, q.date_created
                FROM questions q
                LEFT JOIN users u
                ON q.social_id = u.social_id
                WHERE q.lang = '$question_language'
                AND u.social_id <> '$social_id'
                AND ($languages)
                ORDER BY date_created DESC
                ");
        } else{
            $row1 = $b->query("
                SELECT q.question_id, u.first_name, u.last_name, u.avatar_url, q.audio_url, u.country, q.date_created
                FROM questions q
                LEFT JOIN users u
                ON q.social_id = u.social_id
                WHERE q.lang = '$question_language'
                AND u.country = '$country'
                AND u.social_id <> '$social_id'
                AND ('$languages')
                ORDER BY date_created DESC
                ");
        }
        $i = 0;
        $result[0] = 0;
        foreach($row1 as $r1){
            $result[$i] = array(
                'question_id' => $r1['question_id'],
                'audio_url' => $r1['audio_url'],
                'user_first_name' => $r1['first_name'],
                'user_last_name' => $r1['last_name'],
                'user_avatar_url' => $r1['avatar_url'],
                'user_country' => $r1['country'],
                'date_created' => $r1['date_created']
            );
            $i++;
        }
        $app->response['Content-Type'] = 'application/json';
        echo json_encode( $result );
    });

$app->get(
    '/questions/my',
    function () use ($app) {
        $social_id = $app->request()->get('social_id');
        $a = new db_connect();
        $b = $a->connect();
        $row = $b->query("
            SELECT audio_url, date_created
            FROM questions
            WHERE social_id = '$social_id'
            ORDER BY date_created DESC
        ");
        $i = 0;
        $result[0] = 0;
        foreach($row as $r1){
            $result[$i] = array(
                'audio_url' => $r1['audio_url'],
                'date_created' => $r1['date_created']
            );
            $i++;
        }
        $app->response['Content-Type'] = 'application/json';
        echo json_encode( $result );
    });

$app->get(
    '/audio',
    function () use ($app) {
        $audio_url = $app->request()->get('audio_url');
        $a = new db_connect();
        $b = $a->connect();
        $row = $b->query("
            SELECT audio
            FROM audio
            WHERE audio_url = $audio_url
            ");
        $row = $row->fetchAll();

        $result = array(
            'audio' => $row[0][0]
        );
        $app->response['Content-Type'] = 'application/json';
        echo json_encode( $result );
});

// POST route
$app->post(
    '/auth/register',
    function () use ($app){
        $social_id = $app->request()->get('social_id');
        $first_name = $app->request()->get('first_name');
        $last_name = $app->request()->get('last_name');
        $country = $app->request()->get('country');
        $age = $app->request()->get('age');
        $avatar_url = $app->request()->get('avatar_url');

        if($social_id == NULL||$first_name == NULL||$last_name == NULL){
            $app->response()->status(400);
        }

        $a = new db_connect();
        $a = $a->connect();
        $a->query("
            INSERT INTO users (social_id, first_name, last_name, country, age, avatar_url)
            VALUES($social_id, '$first_name', '$last_name', '$country', $age, '$avatar_url')
            ");
    }
);

$app->post(
    '/messages',
    function () {
        echo 'This is a POST route';
    }
);

$app->post(
    '/answer',
    function () use ($app){
        $json = $app->request()->getBody();
        $assoc = json_decode($json, true);

        $social_id = $assoc['social_id'];
        $question_id = $assoc['question_id'];
        $audio = $assoc['audio'];

        $a = new db_connect();
        $b = $a->connect();

        $row = $b->query("
            SELECT social_id, audio_url
            FROM questions
            WHERE question_id = $question_id
            ");
        $row = $row->fetchAll();
        $c = $row[0][0];
        $d = $row[0][1];
        $b->beginTransaction();
        $b->query("
            INSERT INTO dialogs (social_id_creator, social_id_participant)
            VALUES('$c', '$social_id')
            ");
        $b->query("
            INSERT INTO messages (dialog_id, social_id, audio_url)
            VALUES((SELECT MAX(dialog_id) FROM dialogs), '$c', $d)
            ");
        $b->query("
            INSERT INTO audio (audio)
            VALUES('$audio')
            ");
        $a_url = $b->query("
            SELECT MAX(audio_url)
            FROM audio
            ");
        $b->query("
            INSERT INTO messages (dialog_id, social_id, audio_url)
            VALUES((SELECT MAX(dialog_id) FROM dialogs), '$social_id', (SELECT MAX(audio_url) FROM audio))
            ");
        $b->commit();
        $a_url = $a_url->fetchAll();
        $c = $a_url[0][0];
        $result = array(
            'audio_url' => $c
        );
        $app->response['Content-Type'] = 'application/json';
        echo json_encode( $result );
    }
);

$app->post(
    '/questions',
    function () use ($app){
        $json = $app->request()->getBody();
        $assoc = json_decode($json, true);

        $audio = $assoc['audio'];
        $audio_language = $assoc['audio_language'];
        $social_id = $assoc['social_id'];
        $learning_language = $assoc['learning_language'];

        $a = new db_connect();
        $b = $a->connect();
        $b->beginTransaction();
        $b->query("
            INSERT INTO audio (audio)
            VALUES('$audio');
            ");
        $row = $b->query("
            SELECT MAX(audio_url)
            FROM audio;
            ");
        $b->commit();
        $result = $row->fetchAll();
        $c = $result[0][0];
        $b->query("
            INSERT INTO questions (social_id, audio_url, lang, learning_lang)
            VALUES('$social_id', $c, '$audio_language','$learning_language')
            ");
        $result = array(
            'audio_url' => $c
        );
        $app->response['Content-Type'] = 'application/json';
        echo json_encode( $result );
    }
);

// PUT route
//$app->put(
//    '/questions',
//    function () {
//        echo 'This is a PUT route';
//    }
//);
//
//// PATCH route
//$app->patch('/patch', function () {
//    echo 'This is a PATCH route';
//});
//
//// DELETE route
//$app->delete(
//    '/delete',
//    function () {
//        echo 'This is a DELETE route';
//    }
//);

/**
 * Step 4: Run the Slim application
 *
 * This method should be called last. This executes the Slim application
 * and returns the HTTP response to the HTTP client.
 */
$app->run();

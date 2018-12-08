<?php
$action = 'read';

$auth = Auth::demandLevel('free');

$userid = (int)$args['userid'];

$user = User::read($userid);

if (!$user) {
    HTTPResponse::notFound("User with id $userid");
}

HTTPResponse::ok("User $userid", $user);
?>
<?php

$router->resource('file', 'MediaController', ['only' => ['store', 'show']]);
$router->post('media/thumbnail-path', ['uses' => 'MediaController@getThumbnailPath', 'as' => 'api.media.thumbnail_path']);
$router->post('media/link', ['uses' => 'MediaController@linkMedia', 'as' => 'api.media.link']);
$router->post('media/unlink', ['uses' => 'MediaController@unlinkMedia', 'as' => 'api.media.unlink']);
$router->get('media/all', [
    'as' => 'api.media.all',
    'uses' => 'MediaController@all',
]);

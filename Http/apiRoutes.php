<?php


$router->group(['middleware' => 'media.connection'], function () use($router) {
    $router->resource('file', 'MediaController', ['only' => ['store', 'show']]);
    $router->post('media/thumbnail-path', ['uses' => 'MediaController@getThumbnailPath', 'as' => 'api.media.thumbnail_path']);
    $router->post('media/link', ['uses' => 'MediaController@linkMedia', 'as' => 'api.media.link']);
    $router->post('media/unlink', ['uses' => 'MediaController@unlinkMedia', 'as' => 'api.media.unlink']);
    $router->post('media/unlink-multi', ['uses' => 'MediaController@unlinkMediaMulti', 'as' => 'api.media.unlink-multi']);
    $router->get('media/all', [
        'as' => 'api.media.all',
        'uses' => 'MediaController@all',
    ]);
});

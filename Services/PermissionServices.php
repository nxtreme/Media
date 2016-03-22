<?php

namespace Modules\Media\Services;

use Illuminate\Support\Facades\Request;
use Modules\Core\Contracts\Authentication;

class PermissionServices
{
    private $auth;

    public function __construct(Authentication $auth)
    {
        $this->auth = $auth;
    }

    public function getPermittedConnections()
    {
        $currentlySelected = Request::input('connection');
        $websiteConnections = Config('asgard.media.config.website-connections');

        $allowedConnections = [];
        foreach ($websiteConnections as $websitesConnection => $connectionInfo) {
            if ($this->auth->hasAccess($connectionInfo['permission'] . '.index') === true) {
                $allowedConnections[$websitesConnection] = $connectionInfo;
            }
        }

        return [
            'connections' => $allowedConnections,
            'current' => $currentlySelected,
        ];
    }
}

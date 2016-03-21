<?php namespace Modules\Media\Http\Middleware;

use Closure;
use Modules\Core\Contracts\Authentication;

class MediaConnectionMiddleware {

    private $auth;

    public function __construct(Authentication $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $websiteConnections = Config('asgard.media.config.website-connections');
        $connection = strtolower($request->get('connection'));
        $permissionName = $connection;

        // ###### MAD HACK THAT NEEDS CHANGING HERE ###############
        // currently replacing 'artgallery' with 'content' as content as the current permission name which needs to change later
        if ($connection === 'artgallery' || $connection === 'mysql') {
            $permissionName = 'content';
        }

        if (in_array($connection, $websiteConnections) === false) {
            // Since no connection was specific lets see if we can default to one instead.
            foreach ($websiteConnections as $websitesConnections ) {
                if ($this->auth->hasAccess($websitesConnections . '.index') === true) {
                    return redirect($request->url() . '?connection=' . $websitesConnections);
                }
            }
            return abort(403);
        } else {
            // check if user has access to the connection with the use of the module
            if ($this->auth->hasAccess($permissionName . '.index') !== true) {
                return abort(403);
            }
        }


    	return $next($request);
    }
    
}

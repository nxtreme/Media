<?php namespace Modules\Media\Http\Controllers\Admin;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\Admin\AdminBaseController;
use Modules\Media\Entities\File;
use Modules\Media\Http\Requests\UpdateMediaRequest;
use Modules\Media\Image\Imagy;
use Modules\Media\Image\ThumbnailsManager;
use Modules\Media\Repositories\FileRepository;
use Modules\Media\Services\PermissionServices;

class MediaController extends AdminBaseController
{
    /**
     * @var FileRepository
     */
    private $file;
    /**
     * @var Repository
     */
    private $config;
    /**
     * @var Imagy
     */
    private $imagy;
    /**
     * @var ThumbnailsManager
     */
    private $thumbnailsManager;

    public function __construct(FileRepository $file, Repository $config, Imagy $imagy, ThumbnailsManager $thumbnailsManager)
    {
        parent::__construct();
        $this->file = $file;
        $this->config = $config;
        $this->imagy = $imagy;
        $this->thumbnailsManager = $thumbnailsManager;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request, PermissionServices $permissionServices)
    {
        $files = $this->file->all();

        $config = $this->config->get('asgard.media.config');

        $connectionPermissions = $permissionServices->getPermittedConnections();
        $connection = $request->get('connection');

        return view('media::admin.index', compact('files', 'config', 'connectionPermissions', 'connection'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('media.create');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  File     $file
     * @return Response
     */
    public function edit(Request $request, File $file)
    {
        $thumbnails = $this->thumbnailsManager->all();
        $connection = $request->input('connection');

        return view('media::admin.edit', compact('file', 'thumbnails', 'connection'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  File               $file
     * @param  UpdateMediaRequest $request
     * @return Response
     */
    public function update(File $file, UpdateMediaRequest $request)
    {
        $this->file->update($file, $request->all());

        flash(trans('media::messages.file updated'));

        return redirect()->route('admin.media.media.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  File     $file
     * @internal param int $id
     * @return Response
     */
    public function destroy(Request $request, File $file)
    {
        $this->imagy->deleteAllFor($file);
        $this->file->destroy($file);

        flash(trans('media::messages.file deleted'));

        return redirect()->route('admin.media.media.index', ['connection' => $request->input('connection')]);
    }
}

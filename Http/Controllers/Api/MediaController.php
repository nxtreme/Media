<?php namespace Modules\Media\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Modules\Media\Events\FileWasLinked;
use Modules\Media\Events\FileWasUnlinked;
use Modules\Media\Events\FileWasUploaded;
use Modules\Media\Http\Requests\UploadMediaRequest;
use Modules\Media\Image\Imagy;
use Modules\Media\Repositories\FileRepository;
use Modules\Media\Services\FileService;

class MediaController extends Controller
{
    /**
     * @var FileService
     */
    private $fileService;
    /**
     * @var FileRepository
     */
    private $file;
    /**
     * @var Imagy
     */
    private $imagy;

    public function __construct(FileService $fileService, FileRepository $file, Imagy $imagy)
    {
        $this->fileService = $fileService;
        $this->file = $file;
        $this->imagy = $imagy;
    }

    public function all()
    {
        $files = $this->file->all();

        return [
            'count' => $files->count(),
            'data' => $files,
        ];
    }

    /**
     * Get a single Media resource
     *
     * @param  Request $request
     * @param  integer  $file    The medi's id
     *
     * @return Modules\Media\Entities\File
     */
    public function show(Request $request, $file)
    {
        return $this->file->findOrFail($file);
    }

    /**
     * Get the thumbnail path of a piece of media
     *
     * @param Request $request
     */
    public function getThumbnailPath(Request $request)
    {
        $mediaId = $request->get('mediaId');

        $file = $this->file->whereId($mediaId)->first();
        $thumbnailPath = $this->imagy->getThumbnail($file->path, 'mediumThumb');

        return Response::json([
            'error' => false,
            'result' => ['path' => $thumbnailPath]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param  UploadMediaRequest $request
     * @return Response
     */
    public function store(UploadMediaRequest $request)
    {
        $savedFile = $this->fileService->store($request->file('file'));

        if (is_string($savedFile)) {
            return Response::json(['error' => $savedFile], 409);
        }

        event(new FileWasUploaded($savedFile));

        return Response::json($savedFile->toArray());
    }

    /**
     * Link the given entity with a media file
     * @param Request $request
     */
    public function linkMedia(Request $request)
    {
        $mediaId = $request->get('mediaId');
        $entityClass = $request->get('entityClass');
        $entityId = $request->get('entityId');
        $connection = $request->get('connection');

        $entity = $entityClass::find($entityId);
        $zone = $request->get('zone');
        $entity->files()->attach($mediaId, ['imageable_type' => $entityClass, 'zone' => $zone]);
        $imageable = DB::connection($connection)->table('media__imageables')->whereFileId($mediaId)->whereZone($zone)->whereImageableType($entityClass)->first();
        $file = $this->file->find($imageable->file_id);

        $thumbnailPath = $this->imagy->getThumbnail($file->path, 'mediumThumb');

        event(new FileWasLinked($file, $entity));

        return Response::json([
            'error' => false,
            'message' => 'The link has been added.',
            'result' => ['path' => $thumbnailPath, 'imageableId' => $imageable->id]
        ]);
    }

    /**
     * Remove the record in the media__imageables table for the given id
     *
     * @param Request $request
     */
    public function unlinkMedia(Request $request)
    {
        $imageableId = $request->get('imageableId');
        $connection = $request->get('connection');

        $deleted = DB::connection($connection)->table('media__imageables')->whereId($imageableId)->delete();
        if (! $deleted) {
            return Response::json(['error' => true, 'message' => 'The file was not found.']);
        }

        event(new FileWasUnlinked($imageableId));

        return Response::json(['error' => false, 'message' => 'The link has been removed.']);
    }

    /**
     * Remove the record in the media__imageables table for the given
     * imageable id, specific file id and zone.
     *
     * @param Request $request
     */
    public function unlinkMediaMulti(Request $request)
    {
        $imageableId = $request->get('imageableId');
        $fileId = $request->get('fileId');
        $zone = $request->get('zone');
        $connection = $request->get('connection');

        $deleted = DB::connection($connection)->table('media__imageables')
            ->whereImageableId($imageableId)
            ->whereFileId($fileId)
            ->whereZone($zone)
            ->delete();

        if (! $deleted) {
            return Response::json(['error' => true, 'message' => 'The file was not found.']);
        }

        event(new FileWasUnlinked($imageableId));

        return Response::json(['error' => false, 'message' => 'The link has been removed.']);
    }
}

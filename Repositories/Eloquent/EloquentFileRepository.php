<?php namespace Modules\Media\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Modules\Core\Repositories\Eloquent\EloquentBaseRepository;
use Modules\Media\Entities\File;
use Modules\Media\Helpers\FileHelper;
use Modules\Media\Repositories\FileRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class EloquentFileRepository extends EloquentBaseRepository implements FileRepository
{

    /**
     * @param Model $model
     */
    public function __construct($model)
    {
        parent::__construct($model);
        $connection = Request::input('connection');
        if (isset(Config('database.connections')[$connection]) === true) {
            $this->model->setConnection($connection);
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {

        if (method_exists($this->model, 'translations')) {
            $results = $this->model->orderBy('created_at', 'DESC')->get();
            $results->load('translations');
            return $results;
        }
        return $this->model->orderBy('created_at', 'DESC')->get();
    }

    /**
     * If a method does not exist on the repository
     * try it directly on the model.
     * THis is mostly because I need the where() method
     *
     * @param  string $method
     * @param  array $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        // try call the method locally first
        if (method_exists($this, $method)) {
            return $this->$method(expand($args));
        }

        // then try calling the method on the model
        return call_user_func_array([$this->model, $method], $args);
    }

    /**
     * Update a resource
     * @param  File  $file
     * @param $data
     * @return mixed
     */
    public function update($file, $data)
    {
        $file->update($data);

        return $file;
    }

    /**
     * Create a file row from the given file
     * @param  UploadedFile $file
     * @return mixed
     */
    public function createFromFile(UploadedFile $file)
    {
        $fileName = FileHelper::slug($file->getClientOriginalName());

        $exists = $this->model->where('filename', $fileName)->first();

        if (is_null($exists) !== true) {
            throw new \InvalidArgumentException('File slug already exists');
        }

        $this->model->filename = $fileName;
        $this->model->path = config('asgard.media.config.files-path') . "{$fileName}";
        $this->model->extension = $file->guessClientExtension();
        $this->model->mimetype = $file->getClientMimeType();
        $this->model->filesize = $file->getFileInfo()->getSize();
        $this->model->folder_id = 0;
        $this->model->save();

        return $this->model;
    }

    public function destroy($file)
    {
        $file->delete();
    }

    /**
     * Find a file for the entity by zone
     * @param $zone
     * @param object $entity
     * @return object
     */
    public function findFileByZoneForEntity($zone, $entity)
    {
        foreach ($entity->files as $file) {
            if ($file->pivot->zone == $zone) {
                return $file;
            }
        }

        return '';
    }

    /**
     * Find multiple files for the given zone and entity
     * @param zone $zone
     * @param object $entity
     * @return object
     */
    public function findMultipleFilesByZoneForEntity($zone, $entity)
    {
        $files = [];
        foreach ($entity->files as $file) {
            if ($file->pivot->zone == $zone) {
                $files[] = $file;
            }
        }

        return new Collection($files);
    }
}

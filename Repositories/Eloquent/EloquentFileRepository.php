<?php namespace Modules\Media\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Repositories\Eloquent\EloquentBaseRepository;
use Modules\Media\Entities\File;
use Modules\Media\Helpers\FileHelper;
use Modules\Media\Repositories\FileRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class EloquentFileRepository extends EloquentBaseRepository implements FileRepository
{

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

        $exists = $this->model->whereFilename($fileName)->first();

        if ($exists) {
            throw new \InvalidArgumentException('File slug already exists');
        }

        return $this->model->create([
            'filename' => $fileName,
            'path' => config('asgard.media.config.files-path') . "{$fileName}",
            'extension' => $file->guessClientExtension(),
            'mimetype' => $file->getClientMimeType(),
            'filesize' => $file->getFileInfo()->getSize(),
            'folder_id' => 0,
        ]);
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

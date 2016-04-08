<?php namespace Modules\Media\Entities;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class File extends Model
{
    use Translatable;

    protected $table = 'media__files';
    public $translatedAttributes = ['description', 'alt_attribute', 'keywords'];
    protected $fillable = [
        'description',
        'alt_attribute',
        'keywords',
        'filename',
        'path',
        'extension',
        'mimetype',
        'width',
        'height',
        'filesize',
        'folder_id',
    ];

    /**
     * @param Model $model
     */
    public function __construct()
    {
        parent::__construct();
        $connection = Request::input('connection');
        if (isset(Config('database.connections')[$connection]) === true) {
            $this->setConnection($connection);
        }
    }

    /**
     * Is this file an image?
     *
     * @return bollean
     */
    public function getIsImageAttribute()
    {
        return (boolean) preg_match('/^image\//i', $this->mimetype);
    }
}

<?php namespace Modules\Media\Support\Traits;

trait MediaRelation
{
    /**
     * Make the Many To Many Morph To Relation
     * @return object
     */
    public function files()
    {
        return $this->morphToMany('Modules\Media\Entities\File', 'imageable', 'media__imageables')->withPivot('zone', 'id');
    }

    /**
     * Get all related files for a specific zone.
     *
     * @param  string $zone
     * @return Collection
     */
    public function filesByZone($zone)
    {
        $files = $this->files;

        $filtered = $files->filter(function ($file) {
            return $file->pivot->zone === 'mainImage';
        });

        return $filtered;
    }
}

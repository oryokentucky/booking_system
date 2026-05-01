<?php

namespace App\Models;

use App\Trait\HasRunningNo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Activitylog\LogOptions;
use App\Trait\LatestUpdatedActivityLog;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class BaseModel extends Model implements HasMedia
{
    use InteractsWithMedia, LatestUpdatedActivityLog, LogsActivity, HasRunningNo;

    protected array $loggableAttributes = [];

    /**
     * Get the first media url in the collection and its thumbnail conversion
     *
     * @return array{full_url: mixed, thumb_url: mixed}
     */
    public function getFirstMediaUrlInCollection(string $collectionName = 'default'): array
    {
        if ($this->hasMedia($collectionName)) {

            $media = $this->getFirstMedia($collectionName);

            return [
                'id' => $media->id,
                'full_url' => $media->getFullUrl(),
                'thumb_url' => $media->getFullUrl('thumb'),
                'type' => $media->mime_type,
            ];
        }

        return [
            'id' => null,
            'full_url' => null,
            'thumb_url' => null,
            'mime_type' => null,
        ];
    }

    /**
     * Get all media url in the collection and its thumbnail conversion
     *
     * @return array{full_url: mixed, thumb_url: mixed}
     */
    public function getAllMediaUrlInCollection(string $collectionName = 'default'): array
    {
        if ($this->hasMedia($collectionName)) {
            $medias = $this->getMedia($collectionName);
            $media_data = [];

            foreach ($medias as $media) {
                $media_data[] = [
                    'id' => $media->id,
                    'full_url' => $media->getFullUrl(),
                    'thumb_url' => $media->getFullUrl('thumb'),
                    'type' => $media->mime_type,
                ];
            }

            return $media_data;
        }

        return [];
    }

    /**
     * Frontend-friendly summary of a media collection.
     */
    public function getMediaSummary(string $collectionName = 'default'): array
    {
        return $this->getMedia($collectionName)->map(function ($media) {
            return [
                'id' => $media->id,
                'name' => $media->name ?: $media->file_name,
                'file_name' => $media->file_name,
                'size' => $media->size,
                'url' => $media->getUrl(),
            ];
        })->toArray();
    }

    /**
     * Delete a media item from the given collection by media ID.
     *
     * Returns the deleted media object if found, or null when no media matched.
     */
    public function deleteExistingDocument(int $mediaId, string $collectionName): ?Media
    {
        $media = $this->getMedia($collectionName)->where('id', $mediaId)->first();

        if (! $media) {
            return null;
        }

        $media->delete();

        return $media;
    }

    public function addMultiMediaToCollection(array $files, string $collectionName = 'default')
    {
        foreach ($files as $file) {
            $this->addMedia($file)
                ->usingFileName($collectionName.'_'.date('Y-m-d').'.'.$file->extension())
                ->toMediaCollection($collectionName);
        }
    }

    public function deleteMultiMediaInCollection(array $id, string $collectionName = 'default')
    {
        $excludeMedia = Media::whereNotIn('id', $id)->get();
        $this->clearMediaCollectionExcept($collectionName, $excludeMedia);
    }

    /**
     * Default Media Conversions that will be applied to all collections
     */
    public function registerMediaConversions(?SpatieMedia $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(640)
            ->format('jpg');
    }

    public function getActivitylogOptions(): LogOptions
    {
        $modelName = class_basename($this);

        return LogOptions::defaults()
            ->logOnly($this->loggableAttributes)
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "{$modelName} {$eventName}");
    }

    /**
     * Log a custom activity event
     *
     * @param string $event Event name (e.g., 'submitted', 'approved', 'rejected', 'activated', 'deactivated')
     * @param string|null $description Optional description for the activity
     * @param array $properties Optional additional properties to store
     * @return \Spatie\Activitylog\Models\Activity
     */
    public function logActivity(string $event, ?string $description = null, array $properties = [])
    {
        $modelName = class_basename($this);

        return activity()
            ->performedOn($this)
            ->withProperties($properties)
            ->event($event)
            ->log($description ?? "{$modelName} {$event}");
    }

    /**
     * Convert date from d-m-Y format to Y-m-d format for database storage
     *
     * @param  string|null  $date  Date in d-m-Y format (e.g., 06-01-2026)
     * @return string|null Date in Y-m-d format (e.g., 2026-01-06) or null
     */
    public function convertDateForDatabase(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            return \Carbon\Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Convert date from Y-m-d format to d-m-Y format for display
     *
     * @param  string|null  $date  Date in Y-m-d format (e.g., 2026-01-06)
     * @return string|null Date in d-m-Y format (e.g., 06-01-2026) or null
     */
    public function convertDateForDisplay(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($date)->format('d-m-Y');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Convert multiple date fields from d-m-Y to Y-m-d format
     *
     * @param  array  $data  Data array containing date fields
     * @param  array  $dateFields  Array of field names that contain dates
     * @return array Data array with converted dates
     */
    public function convertDatesForDatabase(array $data, array $dateFields): array
    {
        foreach ($dateFields as $field) {
            if (isset($data[$field]) && ! empty($data[$field])) {
                $data[$field] = $this->convertDateForDatabase($data[$field]);
            }
        }

        return $data;
    }
}

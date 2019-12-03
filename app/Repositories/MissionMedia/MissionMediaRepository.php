<?php
namespace App\Repositories\MissionMedia;

use App\Repositories\MissionMedia\MissionMediaInterface;
use App\Models\MissionMedia;
use Illuminate\Support\Collection;
use App\Helpers\S3Helper;

class MissionMediaRepository implements MissionMediaInterface
{
    /**
     * @var App\Models\MissionMedia
     */
    public $missionMedia;

    /**
     * @var App\Helpers\S3Helper
     */
    private $s3helper;

    /**
     * Create a new MissionMedia repository instance.
     *
     * @param  App\Models\MissionMedia $missionMedia
     * @param  App\Helpers\S3Helper $s3helper
     * @return void
     */
    public function __construct(
        MissionMedia $missionMedia,
        S3Helper $s3helper
    ) {
        $this->missionMedia = $missionMedia;
        $this->s3helper = $s3helper;
    }
    
    /**
     * Save media images
     *
     * @param array $mediaImages
     * @param string $tenantName
     * @param int $missionId
     * @return void
     */
    public function saveMediaImages(array $mediaImages, string $tenantName, int $missionId): void
    {
        $isDefault = 0;
        foreach ($mediaImages as $value) {
            $filePath = $this->s3helper->uploadFileOnS3Bucket($value['media_path'], $tenantName);
            // Check for default image in mission_media
            $default = (isset($value['default']) && ($value['default'] !== '')) ? $value['default'] : '0';
            if ($default === '1') {
                $isDefault = 1;
                $media = array('default' => '0');
                $this->missionMedia->where('mission_id', $missionId)->update($media);
            }
          
            $missionMedia = array(
                    'mission_id' => $missionId,
                    'media_name' => basename($filePath),
                    'media_type' => pathinfo($filePath, PATHINFO_EXTENSION),
                    'media_path' => $filePath,
                    'default' => $default
                );
            $this->missionMedia->create($missionMedia);
            unset($missionMedia);
        }

        if ($isDefault ===  0) {
            $mediaData = $this->missionMedia->where('mission_id', $missionId)
            ->orderBy('mission_media_id', 'ASC')->first();
            $missionMedia = array('default' => '1');
            $this->missionMedia->where('mission_media_id', $mediaData->mission_media_id)->update($missionMedia);
        }
    }

    /**
     * Save media vodeos
     *
     * @param array $mediaVideos
     * @param int $missionId
     * @return void
     */
    public function saveMediaVideos(array $mediaVideos, int $missionId): void
    {
        foreach ($mediaVideos as $value) {
            $missionMedia = array('mission_id' => $missionId,
                                  'media_name' => $value['media_name'],
                                  'media_type' => 'mp4',
                                  'media_path' => $value['media_path']);
            $this->missionMedia->create($missionMedia);
            unset($missionMedia);
        }
    }

    /**
     * Update media images
     *
     * @param array $mediaImages
     * @param string $tenantName
     * @param int $missionId
     * @return void
     */
    public function updateMediaImages(array $mediaImages, string $tenantName, int $missionId): void
    {
        $isDefault = 0;
        foreach ($mediaImages as $value) {
            $filePath = $this->s3helper->uploadFileOnS3Bucket($value['media_path'], $tenantName);
            // Check for default image in mission_media
            $default = (isset($value['default']) && ($value['default'] !== '')) ? $value['default'] : '0';
            if ($default === '1') {
                $isDefault = 1;
                $media = array('default' => '0');
                $this->missionMedia->where('mission_id', $missionId)->update($media);
            }
            
            $missionMedia = array('mission_id' => $missionId,
                                  'media_name' => basename($filePath),
                                  'media_type' => pathinfo($filePath, PATHINFO_EXTENSION),
                                  'media_path' => $filePath,
                                  'default' => $default);
            
            $this->missionMedia->createOrUpdateMedia(['mission_id' => $missionId,
             'mission_media_id' => $value['media_id']], $missionMedia);
            unset($missionMedia);
        }
        $defaultData = $this->missionMedia->where('mission_id', $missionId)
                                    ->where('default', '1')->count();
                                    
        if (($isDefault === 0) && ($defaultData === 0)) {
            $mediaData = $this->missionMedia->where('mission_id', $missionId)
                        ->orderBy('mission_media_id', 'ASC')->first();
            $missionMedia = array('default' => '1');
            $this->missionMedia->where('mission_media_id', $mediaData->mission_media_id)->update($missionMedia);
        }
    }

    /**
     * Update media videos
     *
     * @param array $mediaVideos
     * @param int $id
     * @return void
     */
    public function updateMediaVideos(array $mediaVideos, int $id): void
    {
        foreach ($mediaVideos as $value) {
            $missionMedia = array('mission_id' => $id,
                                  'media_name' => $value['media_name'],
                                  'media_type' => 'mp4',
                                  'media_path' => $value['media_path']);

            $this->missionMedia->createOrUpdateMedia(['mission_id' => $id,
             'mission_media_id' => $value['media_id']], $missionMedia);
            unset($missionMedia);
        }
    }

    /**
     * Remove mission media
     *
     * @param int $mediaId
     * @return void
     */
    public function deleteMedia(int $mediaId): bool
    {
        $mediaStatus = $this->missionMedia->deleteMedia($mediaId);
        return $mediaStatus;
    }
}

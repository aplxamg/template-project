<?php

namespace app\models;

use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\imagine\Image;
use app\components\helpers\Users;
use app\components\helpers\Data;
/**
 * This is the model class for table "asset".
 *
 * @property integer $id
 * @property integer $owner
 * @property string $media_type
 * @property string $file_url
 * @property string $tiny_url
 * @property string $small_url
 * @property string $medium_url
 * @property string $large_url
 * @property double $file_size
 * @property string $ins_time
 * @property string $up_time
 */
class Asset extends \yii\db\ActiveRecord
{
    public $file;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'asset';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['owner', 'media_type', 'file_url'], 'required'],
            [['owner'], 'integer'],
            [['media_type'], 'string'],
            [['file_size'], 'number'],
            [['ins_time', 'up_time'], 'safe'],
            [['file_url', 'tiny_url', 'small_url', 'medium_url', 'large_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'owner' => 'Owner',
            'media_type' => 'Media Type',
            'file_url' => 'File Url',
            'tiny_url' => 'Tiny Url',
            'small_url' => 'Small Url',
            'medium_url' => 'Medium Url',
            'large_url' => 'Large Url',
            'file_size' => 'File Size',
            'ins_time' => 'Ins Time',
            'up_time' => 'Up Time',
        ];
    }

    public function upload()
    {
        // Get user identity
        $identity = Users::initUser();
    }

    /************************************************************
    *******************        Private Functions     ****************
    *************************************************************/

    /**
    *   generateAssetDirectory()
    *   Generates directory for asset uploaded.
    *
    *   @param    array $assetData - contains asset data
    *   @param    string $size - tiny/small/medium/large/original
    *
    *   @return Directory location for asset to be saved
    */
    private function generateAssetDirectory($assetData, $size = 'original')
    {
        $saveDir['file_url'] = 'resources/uploads/' .  $assetData->media_type . 's/';
        $saveDir['file_url'] .= $assetData->owner . '/' . $assetData->id;
        $saveDir['file_url_full'] = $saveDir['file_name'] = null;

        if ($assetData->media_type == 'video') {
            $saveDir['file_url'] = 'resources/uploads/videos/original';
        }

        if (!file_exists($saveDir['file_url'])) {
            mkdir($saveDir['file_url'], 0777, true);
        }

        if ($size == 'original') {
            if ($assetData->media_type == 'video') {
                $videoFileName = date('YmdHis', strtotime($assetData->ins_time)) . '_' . md5($assetData->id);
                $saveDir['file_name'] = $videoFileName . '.' . $assetData->file->extension;
            } else {
                $saveDir['file_name'] = $size . '.' . $assetData->file->extension;
            }
        } else {
            $fileExt = pathinfo($assetData->file_url, PATHINFO_EXTENSION);
            $saveDir['file_name'] = $size . '.' . $fileExt;
        }
        $saveDir['file_url'] .= '/' . $saveDir['file_name'];

        // Some setups need full save path
        $root = getcwd();
        $saveDir['file_url_full'] = $root . '/' . $saveDir['file_url'];

        return $saveDir;
    }

    /**
    *   generateAssetDirectory()
    *   Generates tiny/small/medium/large thumbnails.
    *
    *   @param  array $assetData - contains asset data
    *
    *   @return Save image to set directory and to database
    */
    public function generateThumbnails($assetData)
    {
        // Initialize Variables
        $_assetModel = new Asset;
        $params     = ['id' => $assetData->id, 'owner' => $assetData->owner];
        $assetModel = Data::findRecords($_assetModel, null, $params);

        $saveCount    = 4;
        $sizeArray    = ['tiny' => 80, 'small' => 320, 'medium' => 678, 'large' => 980];
        $quality      = ['quality' => 100];
        $resizeOption = '';

        // Identify what type of calculation should be done on image based on its original size
        list($oWidth, $oHeight) = getimagesize($assetData->file_url);
        if ($oWidth > $oHeight) {
            $resizeOption = 'maxwidth';
        } elseif ($oWidth < $oHeight) {
            $resizeOption = 'maxheight';
        } else {
            $resizeOption = 'exact';
        }

        // Generate thumbnails
        $resizeArea = [];
        foreach ($sizeArray as $key => $size) {
            $saveDir = $this->generateAssetDirectory($assetData, $key);
            $saveDirFull = "http://res.sprasia.dev/". $saveDir['file_url'];
            //$saveDirFull = "http://www.pista-itv.com/". $saveDir['file_url'];
            switch ($key) {
                case 'tiny':
                    $assetModel->tiny_url = $saveDirFull;
                    break;
                case 'small':
                    $assetModel->small_url = $saveDirFull;
                    break;
                case 'medium':
                    $assetModel->medium_url = $saveDirFull;
                    break;
                case 'large':
                    $assetModel->large_url = $saveDirFull;
                    break;
                default:
                    break;
            }
            // Calculate height and width the image should be resized to
            $resizeArea = $this->resizeTo($size, $size, $oWidth, $oHeight, $resizeOption);
            if (Image::thumbnail($assetData->file_url, $resizeArea['width'], $resizeArea['height'])
                ->save($saveDir['file_url'], $quality)) {
                $saveCount--;
            } else {
                return false;
            }
        }

        if ($saveCount == 0) {
            $assetModel->status = 'active';
            $assetModel->up_time = Yii::$app->formatter->asDatetime('now');
            return ($assetModel->save()) ? true : false;
        } else {
            return false;
        }
    }

    /**
     *  resizeTo()
     *  Reference: http://www.paulund.co.uk/resize-image-class-php
     *  Resize the image to these set dimensions
     *
     *  @param  int $width            - Max width of the image
     *  @param  int $height           - Max height of the image
     *  @param  int $origWidth      - Original width of the image
     *  @param  int $origHeight     - Original height of the image
     *  @param  string $resizeOption - Scale option for the image
     *
     *  @return Resize area (width and height)
     */
    public function resizeTo($width, $height, $origWidth, $origHeight, $resizeOption = 'default')
    {
        $resizeArea = [];
        switch (strtolower($resizeOption)) {
            case 'exact':
                $resizeArea['width']  = $width;
                $resizeArea['height'] = $height;
                break;
            case 'maxwidth':
                $resizeArea['width']  = $width;
                $resizeArea['height'] = $this->resizeHeightByWidth($width, $origWidth, $origHeight);
                break;
            case 'maxheight':
                $resizeArea['width']  = $this->resizeWidthByHeight($height, $origWidth, $origHeight);
                $resizeArea['height'] = $height;
                break;
            default:
                if ($origWidth > $width || $origHeight > $height) {
                    if ($origWidth > $origHeight) {
                        $resizeArea['width'] = $width;
                        $resizeArea['height'] = $this->resizeHeightByWidth($width, $origWidth, $origHeight);
                    } elseif ($origWidth < $origHeight) {
                        $resizeArea['width']  = $this->resizeWidthByHeight($height, $origWidth, $origHeight);
                        $resizeArea['height'] = $height;
                    } else {
                        $resizeArea['width']  = $width;
                        $resizeArea['height'] = $height;
                    }
                } else {
                    $resizeArea['width'] = $width;
                    $resizeArea['height'] = $height;
                }
                break;
        }
        return $resizeArea;
    }

    /**
     *  resizeHeightByWidth()
     *  Reference: http://www.paulund.co.uk/resize-image-class-php
     *  Get the resized height from the width keeping the aspect ratio
     *
     *  @param  int $width - Max image width
     *  @param  int $origWidth      - Original width of the image
     *  @param  int $origHeight     - Original height of the image
     *
     *  @return Height keeping aspect ratio
     */
    private function resizeHeightByWidth($width, $origWidth, $origHeight)
    {
        return floor(($origHeight / $origWidth) * $width);
    }

    /**
     *    resizeWidthByHeight()
     *     Reference: http://www.paulund.co.uk/resize-image-class-php
     *     Get the resized width from the height keeping the aspect ratio
     *
     *     @param  int $height - Max image height
     *     @param  int $origWidth      - Original width of the image
     *     @param  int $origHeight     - Original height of the image
     *
     *     @return Width keeping aspect ratio
     */
    private function resizeWidthByHeight($height, $origWidth, $origHeight)
    {
        return floor(($origWidth / $origHeight) * $height);
    }
}

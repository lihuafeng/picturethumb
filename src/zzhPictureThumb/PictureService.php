<?php
/**
 * Created by PhpStorm.
 * User: 华
 * Date: 2020/5/11
 * Time: 13:44
 */

namespace zzhPictureThumb;

/**
 * Class PictureService
 * @package common\services
 */
class PictureService
{
    public $thumbOutPath;

    public $isPrint=false;//是否输出打印在浏览器中 默认false

    /**
     * 设置在浏览器中打印
     * @return $this
     */
    public function setBrowserPrint()
    {
        $this->isPrint=true;
        return $this;
    }
    /**
     * 获取内存图片资源
     * @param $pic
     * @return mixed
     */
    public function makeInternalImg($pic)
    {
        $imgInfo=getimagesize($pic);
        $imgType=image_type_to_extension($imgInfo[2],false);
        $type=$imgInfo['mime'];
        $resource=str_replace('/','createfrom',$type);
        $srcImg=$resource($pic);
        $arr['imgInfo']=$imgInfo;
        $arr['imgType']=$imgType;
        $arr['mime']=$type;
        $arr['src_img']=$srcImg;
        return $arr;
    }

    /**
     * @param $picPath //待缩放的图片路径
     * @param $width //缩放的宽度
     * @param $height //缩放的高度
     * @param bool $rotate //图片是否旋转
     * @return bool|string
     */
    public function pictureThumb($picPath,$width,$height,$rotate=false)//图片缩放
    {
        if(!file_exists($picPath)) return false;
        $setRes=$this->setThumbImgPath($picPath,$width,$height);
        if(!is_null($setRes)) return $this->thumbOutPath;
        $imageInfo=$this->makeInternalImg($picPath);
        $picWidth = $imageInfo['imgInfo'][0];
        $picHeight = $imageInfo['imgInfo'][1];
        //根据参数$width和height值，换算出等比例缩放的宽度和高度
        $resizeWidthTag=$resizeHeightTag=false;
        $widthRatio=$heightRatio=0;
        $ratio=0;//压缩比例
        if($width && $picWidth>$width){
            $widthRatio = $width/$picWidth;
            $resizeWidthTag = true;
        }
        if($height && $picHeight>$height){
            $heightRatio = $height/$picHeight;
            $resizeHeightTag = true;
        }
        if($resizeWidthTag && $resizeHeightTag){
            if($widthRatio<$heightRatio)
                $ratio = $widthRatio;
            else
                $ratio = $heightRatio;
        }
        if($resizeWidthTag && !$resizeHeightTag)
            $ratio = $widthRatio;
        if($resizeHeightTag && !$resizeWidthTag)
            $ratio = $heightRatio;
        $newWidth = $picWidth * $ratio;
        $newHeight = $picHeight * $ratio;
        //在内存中建立一个宽$width,高$height的真色彩画布
        $imageThumb=imagecreatetruecolor($newWidth,$newHeight);
        //将原图复制到新建的真色彩的画布上，并且按一定比例压缩
        imagecopyresampled($imageThumb,$imageInfo['src_img'],0,0,0,0,$newWidth,$newHeight,$imageInfo['imgInfo'][0],$imageInfo['imgInfo'][1]);
        imagedestroy($imageInfo['src_img']);
        if($rotate){
            $resource=$this->imageRotate($imageThumb,$degrees=90,$bgd_color=0);//旋转图片
            if(!$resource){
                return false;
            }
            $imageInfo['src_img']=$resource;
        }else{
            $imageInfo['src_img']=$imageThumb;
        }
        $this->destroyImg($imageInfo,$imageThumb);
        return $this->thumbOutPath;
    }

    /**
     * @param $source
     * @param int $degrees //旋转角度
     * @param int $bgd_color
     * @return false|resource 返回旋转后的图片资源或false
     */
    public function imageRotate($source,$degrees=0,$bgd_color=0)//图片旋转
    {
        //图片旋转
        return imagerotate($source,$degrees, $bgd_color);
    }

    /**
     * 销毁内存中的图片并在浏览器中输出或保存到本地
     * @param $imgInfo
     * @param $destroyImg //要销毁的图片
     * @param $outImg
     */
    public function destroyImg($imgInfo,$destroyImg)
    {
        $func="image{$imgInfo['imgType']}";
        if($this->isPrint){
            header('Content-type:'.$imgInfo['mime']);
            //浏览器输出
            $func($imgInfo['src_img']);
        }else{
            //保存图片
            $func($imgInfo['src_img'],$this->thumbOutPath);
        }
        //销毁图片
        imagedestroy($destroyImg);
    }

    /**
     * 设置压缩后的图片存放路径
     * @param $originImgPath
     * @param $width
     * @param $height
     * @return null|string
     */
    public function setThumbImgPath($originImgPath,$width,$height)
    {
        $pathInfo=pathinfo($originImgPath);
        $this->thumbOutPath=$pathInfo['dirname'].'/thumb_w_'.$width.'_h_'.$height.'_'.$pathInfo['basename'];
        if(file_exists($this->thumbOutPath)) return $this->thumbOutPath;
        return null;
    }
}
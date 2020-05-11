<?php
require_once './vendor/autoload.php';
use zzhPictureThumb\DataProvider;
use zzhPictureThumb\PictureService;

var_dump(DataProvider::client(PictureService::class)->pictureThumb('./pic/1.jpg',500,500));
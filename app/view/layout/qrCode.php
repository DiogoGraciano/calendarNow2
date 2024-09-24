<?php
namespace app\view\layout;

use app\view\layout\abstract\pagina;
use chillerlan\QRCode\QRCode as QRCodeQRCode;

class qrCode extends pagina
{
    public function __construct(string $link,string $name = "qrCode",string $title = "",string $subtitle="")
    {
        $this->setTemplate("qrCode.html");
        $this->tpl->name = $name;
        $this->tpl->title = $title;
        $this->tpl->subtitle = $subtitle;
        $this->tpl->qrcode = (new QRCodeQRCode())->render($link);
    }
}

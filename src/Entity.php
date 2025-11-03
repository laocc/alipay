<?php

namespace laocc\alipay;

use esp\error\Error;
use function esp\helper\root;

class Entity
{
    public string $appid;
    public string $mchid;
    public string $publicSerial;
    public string $privateSerial;
    public string $aesKey;
    public bool $debug;

    public function __construct(array $conf)
    {
        $this->appid = $conf['appid'];
        $this->mchid = $conf['mchid'] ?? $conf['mchID'];
        $this->aesKey = $conf['aeskey'] ?? ($conf['aesKey'] ?? '');
        $this->debug = boolval($conf['debug'] ?? 0);
        $publicSerial = root($conf['publicSerial']);
        $privateSerial = root($conf['privateSerial']);

        if (!is_file($publicSerial)) {
            throw new Error("{$conf['publicSerial']} not exist");
        }
        if (!is_file($privateSerial)) {
            throw new Error("{$conf['privateSerial']} not exist");
        }
        $this->publicSerial = file_get_contents($publicSerial);
        $this->privateSerial = file_get_contents($privateSerial);
    }
}
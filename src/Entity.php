<?php

namespace laocc\alipay;

use esp\error\Error;
use function esp\helper\root;

class Entity
{

    /**
     * 用户的公私钥，用工具创建的，然后上传公钥到支付宝
     */
//    private string $pubCert = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAgKNTn3B1+6UPziqfG5kZAiVkKKeuRS5r0UoWHVImuC+JsotHxGkLUcvusQBX8HMRwuezchpbw+VscIQNz5c7P8emFg58bdEJFK4Juz0ZfHoc+lCimKlQot3cBAZt59QKZzQDwtELnm6aD9UTpybwlcbR/MH9Nhz0kUvuxwtHSsAkZi6lo8H3MVTFRJEuauQ/xcWcaXi4mdAqNbJloJrQ23O6kHci0dNKP2/wo3F7CzOV2PgqwzmEFCq1Wz4S3s8GCXlwtpiC0x548OPgQbx+n81ipWBpom84tjWGKgZ165tMJDJrbu7cnIagVDNY5xhdbSiKNlUnC6nw8G5g6sC17wIDAQAB';
//    private string $priCert = 'MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCAo1OfcHX7pQ/OKp8bmRkCJWQop65FLmvRShYdUia4L4myi0fEaQtRy+6xAFfwcxHC57NyGlvD5WxwhA3Plzs/x6YWDnxt0QkUrgm7PRl8ehz6UKKYqVCi3dwEBm3n1ApnNAPC0QuebpoP1ROnJvCVxtH8wf02HPSRS+7HC0dKwCRmLqWjwfcxVMVEkS5q5D/FxZxpeLiZ0Co1smWgmtDbc7qQdyLR00o/b/CjcXsLM5XY+CrDOYQUKrVbPhLezwYJeXC2mILTHnjw4+BBvH6fzWKlYGmibzi2NYYqBnXrm0wkMmtu7tychqBUM1jnGF1tKIo2VScLqfDwbmDqwLXvAgMBAAECggEALiluMsMKs7kyCMvmuOKhxNFiNeymbxEPg9VQRklat7HnefjdUjBX7Yx/JWl4JUNF1mmLTaED2TKVTXM1+Y7Npj+g7D1ajZX76j3iJBzy7mZry6/wCVSJKUjwUUl1W0IOUaaqyth1kO8jvha6rLsejsEATfHXSfnuEc3r6+WyObJjHBv7uw6S19g1p5gryY458FWdOLTmxoiqTGu1wqDUn5ZugexFD9dIqrTaQ6GbWwh5KCQQ9B8IOHLFzqn1usMHmp44Gk77PZySWerBzq5QHsxDajRgHxWGQ/+fsmrfx7pCnjiU0ay/VWPYcTwoCBPNagRf9qq3drAmNDrU8rvMQQKBgQDGSzk9UMi/xcFrkB19Ajv4Z4uhmN1WEoHmQLw/L62qDilzjP4b+lMhoY4XIAPia822urB6NiHtpqM2rXesU1oV4ZoFB2f/4bKCCAKdLqALVWqTgefnN+ycO8EPG+YbSoyNp1xRnEnLeLPGxztkciGpWUQ4p/HR0Nu3zbZd6MRn/wKBgQCmEshjQxPi8iZUMvZpAL8OaQH9nqn6Dso9Xs9Uzwi4o2nz2J6t17FaxrUOm0StthtDBEXOM1qjIDB/NRpz0SLWXImjBPQliniGyTamLiuuV42QbytUSkWYbyg/6O+hHZtE7/e/MG/ka/Vbo8cQ87a0GffY073afmhIodNodpoyEQKBgQDFiyEd9YHYkuELEEpgRD6MTFNtjIVL/yoLwYgIBq6i0HL5G4f3RV1WDsUoQou8IJuSo8+2IIGSaMUGbq/fLDZ9v/+ZbPRtlWIpamN4RX/JarG++9aUoUEFr/232JpXq9/0Kruszd8yZi/rIoYHni/srvJo4t7koIaTSMuaKI6p7wKBgFym7/tJCgg8Vpv1DrpIO0870GuBoI0swTH5+ivzkYcJTGxJt0V/p6fgYlTy1R8hERaThKpkxjVqGQRvSQHCdPApEnTLp7ZmCZYRHhcafS44FHe3PfI5uZgws8DsIPy/Osf8JTkaOeKzRGDK/516irzyG+9xOmNy0JVtCzglqyMxAoGAFZ/7KS91/et/nKAhVl0AjqvL65JlFZw67mN00bt3wKWmcBYrtfc6Qr2uM8Ku4pNJyIMBlHf1/8qsXIfhxBoLFLMrASKuRZYZ9y7zCNTAMpkmzKCe3mgvMi0C7H8lB89fM/9uoG/VBQYG70NSpukKeibwyV30yYkE6U2WNHjt4FA=';

    /**
     * 上传公钥后得到的支付宝公钥
     *
     */
//    private string $aliPub = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAmFH5DJ9wTlrTW5+X5+m0Ykx0MGAqNQWjd8+h2w3pZWhpVGCGDvDMibzfaF2MFQPWSqXzCl+05fCcx1jv1YgEHWWbraUBJ3KaPcGWOye8m9AnoHaGHmmJbrtpUwUUbQsj5xP8SYl3fyvi+qaWDxvcter5AmCV0ZoPQMDlULL7txsMU722ruGsi8rr9+U9dmNYvV7juiqAE04E4eRBB40yiiiEUPSofxx4TUfdU2QNuF+/7JE0ofpqo5xLVYweleHPXtygve4JnNWAy2jitnkt1r9fqb+r0N3u0lJ4INSoP16PA/sX7BcWQGkuT51E4diwA732HtBgp0EwaPw/VeDY6QIDAQAB';

    public string $appid;
    public string $mchid;
    public string $publicSerial;
    public string $privateSerial;
    public bool $debug;

    public function __construct(array $conf)
    {
        $this->appid = $conf['appid'];
        $this->mchid = $conf['mchid'] ?? $conf['mchID'];
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
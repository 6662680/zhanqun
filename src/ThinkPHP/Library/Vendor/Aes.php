<?php
/**
 * 加密类
 * @author 
 *
 */
class Aes
{
    /*
     * 加密
     */
    public static function encrypt($input, $key) {
        $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $input = self::pkcs5_pad($input, $size);
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
        $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, Aes::hextobin($key), $iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = base64_encode($data);
        return $data;
    }
 
    private static function pkcs5_pad ($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }
 
    /*
     * 解密
     */
    public static function decrypt($sStr, $sKey) {
        $decrypted= mcrypt_decrypt(MCRYPT_RIJNDAEL_128, Aes::hextobin($sKey), base64_decode($sStr), MCRYPT_MODE_ECB);
        $dec_s = strlen($decrypted);
        $padding = ord($decrypted[$dec_s-1]);
        $decrypted = substr($decrypted, 0, -$padding);
        return $decrypted;
    }
    
   static function hextobin($hexstr)
    {
    	$n = strlen($hexstr);
    	$sbin="";
    	$i=0;
    	while($i<$n)
    	{
    		$a =substr($hexstr,$i,2);
    		$c = pack("H*",$a);
    		if ($i==0){$sbin=$c;}
    		else {$sbin.=$c;}
    		$i+=2;
    	}
    	return $sbin;
    }
}
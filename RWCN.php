<?php

require_once "../lib.php";

class RW_CN
{
    // 使用Cookie中的UIA鉴权 长期有效 不受注销登录影响
    var $URL = "https://www.rustedwarfare.com/index.php";
    var $UIA = "UIA=" . "XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";
    var $chkcsrfval = "9bfaabfeac4693f9ccf9bca9da87507c";
    var $cookie = "PHPSESSID=" . "iagf827g5bps6r4djq1tdjh2t7";

    /**
     * 签到
     * @return bool|string 操作结果
     */
    function sign()
    {
        return _curl("$this->URL?c=app&a=puyuetian_qiandao:index", 0, 0, $this->UIA);
    }

    /**
     * 登录账户
     * TODO: 验证码
     * @param $username string 用户名
     * @param $password string 密码
     * @return bool|string 操作结果
     */
    function login(string $username, string $password)
    {
        $url = "$this->URL?c=chklogin&return=json&referer=&chkcsrfval=$this->chkcsrfval";
        $code = $this->randomString(32);
        $username = urlencode($username);
        $password = md5(md5($password) . $code);
        $verifycode = $this->getVerifyCode(); //五位数字 e.g 72499
        $body = "_webos=HadSky&chkcsrfval=$this->chkcsrfval&autologin=1&enpw=1&code=$code&username=$username&password=$password&verifycode=$verifycode";
        $this->UIA = "";
        return _curl($url, $body);
    }

    /**
     * js原版寫法
    function randomString(len, chars) {
    len = len || 16;
    var $chars = !chars ? 'qwertyuiopasdfghjklzxcvbnm0123456789' : chars;
    var maxPos = $chars.length;
    var pwd = '';
    for (i = 0; i < len; i++) {
    pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
    }
    return pwd;
    }
     */
    private function randomString($len): string
    {

        $len = $len ?: 16;
        $chars = 'qwertyuiopasdfghjklzxcvbnm0123456789';
        $maxPos = strlen($chars);
        $pwd = '';
        for ($i = 0; $i < $len; $i++) {
            $pwd .= $chars[rand(1, $maxPos)];
        }
        return $pwd;
    }

    function getVerifyCode()
    {
        return _curl("$this->URL?c=app&a=verifycode:index&type=login&rangeverifycode=yes");
    }

    /**
     * 坑钱大转盘
     * @return bool|string 操作结果
     */
    function turnTable()
    {
        return _curl("$this->URL?c=app&a=puyuetian_turntablegame:turn", 0, 0, $this->UIA);
    }

    /**
     * 收藏帖子
     * @param $uid
     * @return bool|string 操作结果
     */
    function collect($uid)
    {
        return _curl("$this->URL?c=center&type=addcollect&uid=$uid&chkcsrfval=$this->chkcsrfval", 0, 0, $this->UIA);
    }

    /**
     * 取消收藏帖子
     * @param $uid
     * @return bool|string 操作结果
     */
    function decollect($uid)
    {
        return _curl("$this->URL?c=center&type=delcollect&uid=$uid&chkcsrfval=$this->chkcsrfval", 0, 0, $this->UIA);
    }

    /**
     * 回复帖子
     * @param $id int 帖子ID
     * @param $content string 回复内容
     * @return bool|string {"state":"ok","msg":"\u53d1\u8868\u6210\u529f","rid":"7447"} 返回rid为帖子ID
     */
    function reply(int $id, string $content)
    {
        $url = "$this->URL?c=post&type=reply&return=json&chkcsrfval=$this->chkcsrfval";
        $body = "_webos=HadSky&chkcsrfval=$this->chkcsrfval&rid=$id&content=$content&=1";
        return _curl($url, $body, 0, $this->UIA);
    }

    function reply_delAll(int $id)
    {
        $html = _curl("https://www.rustedwarfare.com/read-$id-1.html", 0, 0, $this->cookie);//通过PHPSESSID判断帖子回复是否公开可见
        preg_match_all('/<div.*data-id=\"(\d*)\".*class=\"replycontent/', $html, $matches);
        echo $matches[1][0] ?: "未找到回复ID";
        for ($i = 0; $i < count($matches[1]); $i++) {
            echo $this->reply_del($matches[1][$i]);
        }
        return $this->reply_del($matches[1][0]) ?: "";
    }

    /**
     * 删除帖子回复
     * @param $id int 帖子ID
     * @return bool|string {"state":"ok","msg":"操作成功","datas":{"msg":"操作成功"}}
     */
    function reply_del(int $id)
    {
        return $id ? _curl("$this->URL?c=delete&table=reply&field=del&value=auto&id=$id&chkcsrfval=$this->chkcsrfval&json=1", 0, 0, $this->UIA) : null;
    }

    function pvp_auto()
    {
        return ($this->getPVP_Strength("self") <= $this->getPVP_Strength("top")) ? "战力小于TOP1已略过" : $this->pvp($this->getPVPTop());
    }

    /**
     * 取指定对象PVP战力值
     * @param $target
     * @return int 战力值
     */
    function getPVP_Strength($target): int
    {
        $target == "self" ?
            $regex = '/<p.*class=\"strength\".*data-field=\"zhanli\">(\d*?)<\/span><\/p>/' :
            $regex = '/<p.*class=\"strength\">战力：(\d*?)<\/p>/';
        //<p class="strength" title="综合战斗力">战力：<span class="data-field" data-field="zhanli">4160</span></p>
        //<p class="strength">战力：2810</p>
        preg_match_all($regex, $this->getPVPList(), $matches);
        return (int)$matches[1][0];
    }

    /**
     * @return bool|string 天天论剑列表数据
     */
    function getPVPList()
    {
        $url = "$this->URL?c=app&a=puyuetian_tiantianlunjian:index";
        return _curl($url, 0, 0, $this->UIA);
    }

    /**
     * 天天论剑
     * @param $id int 对战ID
     * @return bool|string 对战结果
     */
    function pvp(int $id)
    {
        return _curl("$this->URL?c=app&a=puyuetian_tiantianlunjian:index&s=fight&id=$id&chkcsrfval=$this->chkcsrfval", 0, 0, $this->UIA);
    }

    /**
     * 取现列表中最顶端用户ID
     * @return int|string 用户ID
     */
    function getPVPTop()
    {
        preg_match_all('/<div.*class=\"btn kz\".*onclick=\"tiaozhan\((\d*?),this\)\">/', $this->getPVPList(), $matches);
        $result = $matches[1][0];
        echo "PVPTopID:$result<br />";
        return $result;
    }

    /**
     * 设置UIA
     * @param $uia string 92位固定字符
     */
    function setUIA(string $uia)
    {
        $this->UIA = "UIA=$uia";
    }

}
<?php

Imports("System.Math");

/**
 * 反爬虫模块
 * 
 * 这个模块适用于数据请求不太频繁，但是比较重要的数据源的访问保护
 * 
 * 首先，在服务器端，随机生成一个javascript代码，并得到验证计算结果
 * 然后返回浏览器端，浏览器端使用eval进行动态代码的执行
 * 浏览器端计算出结果之后，将结果返回服务器端，服务器端验证结果
 * 验证成功之后再返回所请求的数据
*/
class ScraperChallenge {

    static $math = [
        ""
    ];

    /** 
     * 随机生成javascript代码，并返回结果
    */
    public static function getChallenge() {

    }
}
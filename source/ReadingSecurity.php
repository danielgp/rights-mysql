<?php

/*
 * The MIT License
 *
 * Copyright 2015 Daniel Popiniuc <danielpopiniuc@gmail.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace danielgp\rights_mysql;

/**
 * This script has been made to extract all users and their grants from Productrion network
 * for an MySQL major version upgrade (5.5.30 to 5.6.10)
 */
class ReadingSecurity
{

    use configurationRightsMySQL,
        \danielgp\common_lib\CommonCode;

    public function __construct() {
        $this->setHeaderNoCache('text/html');
        echo $this->setHeaderCommon();
        $this->connectToMySql($this->configuredMySqlServer());
        echo $this->getMySqlUserGrants($this->getMySqlUsers());
        echo $this->setFooterCommon();
    }

    private function getMySqlUsers() {
        return $this->setMySQLquery2Server($this->queryMySqlUsersList(), 'array_numbered')['result'];
    }

    private function getMySqlUserGrants($listOfMySqlUsers) {
        $sRtn = [];
        foreach ($listOfMySqlUsers as $uVal) {
            if ($this->mySQLconnection->server_version >= 50708) {
                $sRtn[] = $this->setMySQLquery2Server($this->queryShowMySqlUsersCreate([$uVal]), 'value')['result'];
            }
            $universalMySqlGrants = $this->getMySqlUserGrantsUniversal($uVal);
            foreach ($universalMySqlGrants as $crtUserValue) {
                $sRtn[] = $crtUserValue;
            }
        }
        return implode(';<br/>', $sRtn) . ';';
    }

    private function getMySqlUserGrantsUniversal($userValue) {
        $sReturn = [];
        $qry     = $this->queryShowMySqlUsersGrants([$userValue]);
        $result  = $this->setMySQLquery2Server($qry, 'full_array_key_numbered')['result'];
        foreach ($result as $value2) {
            foreach ($value2 as $value3) {
                $sReturn[] = $value3;
            }
        }
        return $sReturn;
    }

    private function queryMySqlUsersList() {
        return 'SELECT CONCAT("\"", `User`, "\"@\"", `Host`, "\"") AS `UserHost` '
                . 'FROM `mysql`.`user` '
                . 'ORDER BY `host`, `user`;';
    }

    private function queryShowMySqlUsersCreate($parameters) {
        return 'SHOW CREATE USER ' . filter_var($parameters[0], FILTER_SANITIZE_STRING) . ';';
    }

    private function queryShowMySqlUsersGrants($parameters) {
        return 'SHOW GRANTS FOR ' . filter_var($parameters[0], FILTER_SANITIZE_STRING) . ';';
    }
}

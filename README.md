# 基本介紹
- Mysqli 小功能製作
- 資料庫匯出/匯入

# 環境
- php 5

# 操作
```php
<?php
$config = [
  'hostname' => 'localhost',
  'username' => 'root', //帳號
  'password' => '', //密碼
  'database' => '', //指定資料庫
  'character' => 'utf8' //y字元
];
$mysqli = new Mysqlifunction($config);

//匯出
$mysqli->backup();

//匯入
$mysqli->reduction($filePath);

```


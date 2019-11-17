# jesp: Join, Evaluate, Sort, and Print csv files

jesp 讓你用一個 (共通的) primary key 把幾個 .csv 檔合併 (join) 起來。
除了可以指定只顯示其中一些欄位之外， 也可以自行以數學式定義新的 (衍生) 欄位。
還可以用數學式指定過濾條件， 只顯示少部分的列。
訪客所看到的最終表格， 點一下欄位名稱就可以把所有的列按照那個欄位排序。

## 安裝試用 ##

1. 安裝/啟用網頁伺服器 -- 例如 apache2。
1. 確認你的網頁已能使用 php。
   (例如我在 ubuntu 18.04 底下， 要先 ```a2enmod php7.2``` 。)
1. 安裝 [Symfony ExpressionLanguage](https://symfony.com/doc/current/components/expression_language.html)。
   以 ubuntu 及 debian 系列為例， 最簡單的方法是
   ```apt install php-symfony-expression-language```
1. 把整個 jesp 目錄放到你的網站下， 例如 /var/www/html/jesp/
1. 在這個目錄底下建捷徑： ```ln -s /usr/share/php/Symfony/Component/ExpressionLanguage```
1. 把瀏覽器指向 http://localhost/jesp/ ， 就會看到一個表格。

顯示的表格是台股近年高現金殖利率個股的清單， 也就是
[自己的明牌自己撈](https://newtoypia.blogspot.com/2018/08/calc.html)
的某日靜態網頁版。 關於這個清單的意義與投資風險，
請自行參考該文； 這裡只是拿它來作為 jesp 的使用範例而已。

## 設定檔 ##

上述網址的完整版應該是 http://localhost/jesp/?c=config.json 
表示從 [config.json](https://github.com/ckhung/jesp/blob/master/config.json)
讀取設定檔。 當你省略 c=... 時， jesp 的 index.php
會自動以同一目錄下的 config.json 作為設定檔。
這可以是一個相對路徑， 甚至可以是一個網址。

設定檔中的 ```csvfiles``` 陣列列出所有 csv 檔的檔名或網址；
```pkey``` 指定 (它們共同的) 主鍵 (primary key) 的名稱。
每個 csv 檔的第一列必須是欄位名稱；
每個 csv 檔內都必須包含這個主鍵欄位。
在這個範例當中有兩個 csv 檔：
181123.csv 是某日台股所有個股收盤價；
div.csv 則是台股所有個股歷年現金股利表。
而它們共同的主鍵則是 「代號」， 例如代表台積電的 2330。

程式會去讀取每個 csv 檔， 並且用這個主鍵把所有的表格 join 在一起。

資料表當中若有文字欄位， 必須把欄位名稱列在```textcols``` 陣列當中；
其餘欄位一律被當成數字處理。

想要顯示在網頁上的欄位請按照順序列在 ```col``` 陣列當中。
每一個欄位可以包含以下資訊：
1. ```name``` ： 在 csv 檔裡面以及在網頁上所顯示的欄位名稱。
2. ```format``` ： 顯示資料/數值時採用的 printf 格式化字串。
3. ```var``` ： 在運算式 (下詳) 裡面， 這個欄位的代號。
   有點像是幫它取一個變數名稱的意思。 ~~僅限用於從 csv 檔中讀進來的既有欄位。~~
4. ```expr``` ： 運算式 (下詳)。 僅限用於新建立的衍生欄位。

想要顯示既有的欄位， 只需要填寫 ```name``` 即可， 其他可省略。
同一個欄位可以重複出現兩次， 例如範例當中的 「名稱」。

也可以用 ```expr``` 定義新的欄位， 它的數值由既有欄位算出， 例如範例中的
```
    {
      "name": "今殖率",
      "expr": "y18/price*100",
      "format": "%.1f"
    },
```
這會在網頁上新增一個 「今殖率」 欄位， 它的值是從
「y18/收盤價*100」 算出來的， 其中的 「price」
就是 「收盤價」 欄位的變數名稱。
新欄位不可以引用彼此， 只能引用既有的欄位。
使用者自訂的新欄位的 ```var``` 只在 ```keep``` 設定
(下詳) 當中有意義。

最後， 在 json 設定檔裡的 ```keep```
可以指定依據某些條件只保留部分的資料列。
例如： ```"keep": "dy_past>6"```
表示只保留 「歷殖率」 大於 6 的資料列。
這裡還可以用 php 語法的 && 及 || 等等撰寫邏輯條件。

## 註 ##

1. 頁面操作： 點選欄位名稱， 可以令資料依據該欄由大到小排列。
   再點一次， 改成由小到大排列。
   若希望採用預設的 「點一次先由小到大、 點第二次由大到小」，
   可修改 jesp.js 的設定， 刪掉 orderSequence 那一段。
1. 數值欄位當中若出現 nan， 整列資料會被略過。
   資料若有欠欄位， 也會被略過。 這個問題以後再改進。
1. 本程式省略各種錯誤檢查。 **即使只是設定檔寫錯， 也有可能讓畫面完全不見。**
   使用者有可能經常需要 ```tail -f /var/log/apache2/error.log``` 以及
   [在瀏覽器裡打開 console](https://www.cyut.edu.tw/~ckhung/b/js/console.php)
   來自行除錯。 程式碼說明：
   [處處訝異的怪怪語言 php](https://newtoypia.blogspot.com/2018/11/php.html)、
   [DataTables 的固定表頭、 排名、 置中](https://newtoypia.blogspot.com/2018/11/datatables.html)。
1. 因為 php 不會分辨整數 2330 跟字串 "2330"，
   因此當這樣長像的數字/字串被拿來當成主鍵時， 會發生問題。
   程式內會把主鍵前面都冠一某個英文字母 (z)；
   在印出時又會把它刪掉。 應該不會影響使用者。
   詳見程式碼中的 keyprefix。

<?php

return array(
    'csvfiles' => array(
	"https://v.im.cyut.edu.tw/~ckhung/saas/stock/price.csv", "div.csv", 
    ),
    'pkey' => "代號",
    'keyprefix' => "s",
    'textcols' => array("代號", "名稱"),
    'col' => array(
	array(
	    'disp' => '代號',
	    'var' => 'sid',
	),
	array(
	    'disp' => '名稱',
	    'var' => 'name',
	),
	array(
	    'disp' => '收盤價',
	    'var' => 'price',
	),
	array(
	    'disp' => '五年均',
	    'var' => 'div_a5',
	    'format' => '%.3f',
	),
	array(
	    'disp' => '五年均B',
	    'var' => 'div_a5B',
	    'expr' => '(y18+y17+y16+y15+y14)/5',
	    'format' => '%.3f',
	),
	array(
	    'disp' => 'y18',
	    'format' => '%.2f',
	),
	array(
	    'disp' => '歷殖率',
	    'var' => 'dy_past',
	    'expr' => 'div_a5/price*100',
	    'format' => '%.1f',
	),
	array(
	    'disp' => '今殖率',
	    'var' => 'dy_ty',
	    'expr' => 'y18/price*100',
	    'format' => '%.1f',
	),
    ),
    'keep' => 'dy_ty>4',
);

?>


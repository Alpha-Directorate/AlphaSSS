<?php 
$I = new AcceptanceTester($scenario);
$I->resizeWindow(1024, 1024);
$I->wantTo('see GF finances');

$I->amOnPage('/');
$I->see('Login','.button');
$I->click("//a[@class='button' and text()='Login']");
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','elen');
$I->fillField('pwd','funkadelicbro87');
$I->click('Log In');
$I->seeCurrentUrlEquals('/browse/elen/');
$I->moveMouseOver("//li[@id='wp-admin-bar-my-account']");
$I->see('Finances', 'li');
$I->moveMouseOver("//a[@class='ab-item' and text()='Finances']");
$I->click('Accounting');
$I->seeCurrentUrlEquals('/browse/elen/finances/');
$I->moveMouseOver("//li[@id='wp-admin-bar-my-account']");
$I->see('Log Out','.ab-item');
$I->click("//a[@class='ab-item' and text()='Log Out']");

// Finances dosn't work for the member
$I->amOnPage('/');
$I->see('Login','.button');
$I->click("//a[@class='button' and text()='Login']");
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','saybb');
$I->fillField('pwd','funkadelicbro87');
$I->click('Log In');
$I->seeCurrentUrlEquals('/browse/saybb/');
$I->moveMouseOver("//li[@id='wp-admin-bar-my-account']");
$I->dontSee('Finances', 'li');
$I->amOnPage('/browse/saybb/finances/');
$I->see("Not Found");
$I->moveMouseOver("//li[@id='wp-admin-bar-my-account']");
$I->see('Log Out','.ab-item');
$I->click("//a[@class='ab-item' and text()='Log Out']");

// Finances dosn't work for the pre-member
$I->amOnPage('/');
$I->see('Login','.button');
$I->click("//a[@class='button' and text()='Login']");
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','tanya');
$I->fillField('pwd','funkadelicbro87');
$I->click('Log In');
$I->seeCurrentUrlEquals('/register-pre-member/');
$I->moveMouseOver("//li[@id='wp-admin-bar-my-account']");
$I->dontSee('Finances', 'li');
$I->amOnPage('/browse/tanya/finances/');
$I->see("Not Found");
$I->moveMouseOver("//li[@id='wp-admin-bar-my-account']");
$I->see('Log Out','.ab-item');
$I->click("//a[@class='ab-item' and text()='Log Out']");

// Finances works for the administrator
$I->amOnPage('/');
$I->see('Login','.button');
$I->click("//a[@class='button' and text()='Login']");
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','Founder_Counselor');
$I->fillField('pwd','#caRousal.72');
$I->click('Log In');
$I->seeCurrentUrlEquals('/browse/founder_counselor/');
$I->moveMouseOver("//li[@id='wp-admin-bar-my-account']");
$I->see('Finances', 'li');
$I->moveMouseOver("//a[@class='ab-item' and text()='Finances']");
$I->click('Accounting');
$I->seeCurrentUrlEquals('/browse/founder_counselor/finances/');
$I->moveMouseOver("//li[@id='wp-admin-bar-my-account']");
$I->see('Log Out','.ab-item');
$I->click("//a[@class='ab-item' and text()='Log Out']");
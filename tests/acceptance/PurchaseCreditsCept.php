<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('purchase AlphaSSS credits');
$I->amOnPage('/');
$I->see('Register','li');
$I->see('Login','.button');
$I->click("//a[@class='button' and text()='Login']");
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','saybb');
$I->fillField('pwd','funkadelicbro87');
$I->click('Log In');
$I->seeCurrentUrlEquals('/browse/founder_counselor/');
$I->dontSee('Login','.button');
$I->dontSee('Register','li');
$I->seeElement('#wp-admin-bar-user-credits');
?>
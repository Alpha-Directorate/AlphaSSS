<?php 
$I = new AcceptanceTester($scenario);
$I->resizeWindow(1024, 1024);
$I->wantTo('Check the user precondition works');
$I->amOnPage('/');
$I->see('Register','li');
$I->see('Login','.login');
$I->click("//a[@class='login' and text()='Login']");
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','Founder_Counselor');
$I->fillField('pwd','#caRousal.72');
$I->click('Log In');
$I->seeCurrentUrlEquals('/browse/founder_counselor/');
$I->dontSee('Login','.login');
$I->dontSee('Register','li');
$I->moveMouseOver("//div[@class='header-account-login']");
$I->see('Logout');
$I->click('Logout', '.logout');
$I->seeCurrentUrlEquals('/browse/founder_counselor/?loggedout=true');
$I->see('Register','li');
$I->see('Login','.login');
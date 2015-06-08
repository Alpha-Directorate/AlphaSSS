<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('Check the user precondition works');
$I->amOnPage('/');
$I->see('Register','li');
$I->see('Login','.button');
$I->click("//a[@class='button' and text()='Login']");
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','Founder_Counselor');
$I->fillField('pwd','#caRousal.72');
$I->click('Log In');
$I->seeCurrentUrlEquals('/browse/founder_counselor/');
$I->dontSee('Login','.button');
$I->dontSee('Register','li');
$I->moveMouseOver("//a[@title='My Account']");
$I->see('Log Out','.ab-item');
$I->click("//a[@class='ab-item' and text()='Log Out']");
$I->seeCurrentUrlEquals('/browse/founder_counselor/?loggedout=true');
$I->see('Register','li');
$I->see('Login','.button');



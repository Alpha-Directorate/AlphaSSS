<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('Check the user precondition works');
$I->amOnPage('/');
$I->see('Register','li');
$I->see('Login','.button');
$I->click('Login','.button');
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','Founder_Counselor');
$I->fillField('pwd','#caRousal.72');
$I->click('Log In');
$I->seeCurrentUrlEquals('/browse/founder_counselor/');
$I->dontSee('Login','.button');
$I->dontSee('Register','li');
$I->click('Log Out');
$I->seeCurrentUrlEquals('/browse/founder_counselor/?loggedout=true');
$I->see('Register','li');
$I->see('Login','.button');



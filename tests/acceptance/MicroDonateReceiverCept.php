<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('perform actions and see result');
$I->amOnPage('/');
$I->see('Login','.button');
$I->click('Login','.button');
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','saybb');
$I->fillField('pwd','funkadelicbro87');
$I->click('Log In');
$I->seeCurrentUrlEquals('/browse/saybb/');
$I->click('Neighborhood', '#user-groups');
$I->see('The Neighborhood','#groups-my-groups');

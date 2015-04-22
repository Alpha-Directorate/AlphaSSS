<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('Check micro donations for group admin');
$I->amOnPage('/');
$I->see('Login','.button');
$I->click('Login','.button');
$I->seeCurrentUrlEquals('/wp/wp-login.php');
$I->fillField('log','saybb');
$I->fillField('pwd','funkadelicbro87');
$I->click('Log In');
$I->seeCurrentUrlEquals('/browse/saybb/');
$I->click('Neighborhood', '#user-groups');
$I->seeCurrentUrlEquals('/browse/saybb/groups/');
$I->see('The Neighborhood','#groups-my-groups');
$I->click('Sex Lovers United');
$I->seeCurrentUrlEquals('/groups/sex-lovers-united/');
$I->see('Homies','#members');

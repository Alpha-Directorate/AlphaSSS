<?php 
$I = new AcceptanceTester($scenario);
$I->resizeWindow(1024, 1024);
$I->wantTo('Verify that home page is works');
$I->amOnPage('/');
$I->see('Where has our privacy online gone?','h1');
$I->seeCurrentUrlEquals('/');
$I->see('Home','a');
$I->click(['link' => 'Home']);
$I->seeCurrentUrlEquals('/');
$I->see('Where has our privacy online gone?','h1');
$I->click('.site-title a');
$I->seeCurrentUrlEquals('/');
$I->see('Where has our privacy online gone?','h1');
$I->dontSeeElement('#wp-admin-bar-user-credits-icon');

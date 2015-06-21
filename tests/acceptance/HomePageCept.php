<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('Verify that home page is works');
$I->amOnPage('/');
$I->see('Where has our privacy online gone?','h1');
$I->seeCurrentUrlEquals('/');
$I->see('Home','a');
$I->click(['link' => 'Home']);
$I->seeCurrentUrlEquals('/');
$I->see('Where has our privacy online gone?','h1');
$I->click(['link' => 'Alpha Social Club']);
$I->seeCurrentUrlEquals('/');
$I->see('Where has our privacy online gone?','h1');
$I->dontSeeElement('#wp-admin-bar-user-credits-icon');

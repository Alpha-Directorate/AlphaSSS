<?php 
$I = new AcceptanceTester($scenario);
$I->wantTo('Verify that home page is works');
$I->amOnPage('/');
$I->see('Where has our privacy online gone?','h1');

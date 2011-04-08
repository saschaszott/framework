<?php
require_once 'PHPUnit/Framework.php';

/**
 * Test class for Opus_Document_Plugin_IdentifierUrn.
 * Generated by PHPUnit on 2011-04-08 at 12:44:41.
 */
class Opus_Document_Plugin_IdentifierUrnTest extends PHPUnit_Framework_TestCase
{
    public function testAutoGenerateUrn() {
        $model = new Opus_Document();
        $model->store();
        
        $this->assertTrue($model->hasField('IdentifierUrn'), 'Model does not have field "IdentifierUrn"');
        $urn = $model->getIdentifierUrn();
        $this->assertNotNull($urn, 'IdentifierUrn is NULL');

        $this->assertEquals(1, count($urn));

        $config = Zend_Registry::get('Zend_Config');
        $urnItem = new Opus_Identifier_Urn($config->urn->nid, $config->urn->nss);
        $checkDigit = $urnItem->getCheckDigit($model->getId());
        $urnString = 'urn:' . $config->urn->nid . ':' . $config->urn->nss . '-' . $model->getId() . $checkDigit;

        $this->assertEquals($urnString, $urn[0]->getValue());
    }
}
?>

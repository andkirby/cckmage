<?php
/**
 * Test of class Kand_Cck_Helper_Data
 *
 * @category    Kand
 * @package     Kand_Cck
 */
class Kand_Cck_Helper_DataTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test class name
     *
     * @var string
     */
    protected $_className = 'Kand_Cck_Helper_Data';

    /**
     * Get mock object
     *
     * @param array $methods
     * @return Kand_Cck_Helper_Data|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getObjectMock(array $methods)
    {
        return $this->getMock($this->_className, $methods);
    }

    /**
     * Test instance
     */
    public function testInstance()
    {
        $this->assertInstanceOf($this->_className, $this->getMock($this->_className));
    }

    /**
     * Test collecting texts in HTML with empty param
     */
    public function testCollectTextsEmpty()
    {
        $methods = array('____');
        $test = $this->_getObjectMock($methods);
        $result = $test->collectTexts('');
        $this->assertEquals(array(), $result);
        $result = $test->collectTexts(null);
        $this->assertEquals(array(), $result);
    }

    /**
     * Test collecting texts in HTML with non-string param
     */
    public function testCollectTextsNonString()
    {
        $methods = array('____');
        $test = $this->_getObjectMock($methods);

        $this->setExpectedException('Exception', 'HTML parameter must a string.');
        $test->collectTexts(array());

        $this->setExpectedException('Exception', 'HTML parameter must a string.');
        $test->collectTexts(new stdClass());
    }

    /**
     * Test collecting texts in HTML
     */
    public function testCollectTextsSuccess()
    {
        $this->markTestIncomplete();
        $methods = array('___');
        $test = $this->_getObjectMock($methods);
        //@startSkipCommitHooks
        $html = <<<HTML
<div>
    <h1>Some Title</h1>
    <span>Span Text</span>
    <a href="#mylink">My pretty nice <em>link</em></a>
    <p>Some long paragraph
    with a <a>link</a></p>
</div>
HTML;
        //@finishSkipCommitHooks

        $result = $test->collectTexts(
            $html
        );

        $this->assertEquals(
            array(
                'Some Title',
                'Span Text',
                'My pretty nice <em>link</em>',
                'Some long paragraph with a <a>link</a>',
            ),
            $result
        );
    }
}

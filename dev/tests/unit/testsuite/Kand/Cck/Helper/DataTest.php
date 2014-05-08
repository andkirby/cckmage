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
        $methods = array('___');
        $test = $this->_getObjectMock($methods);

        //@startSkipCommitHooks
        $html = <<<HTML
Some text in the beginning.
<div class="some-class" style="background-color: #000055">
    <h1>Some Title</h1>
    <span>Span Text</span>
    <a href="#mylink">My pretty nice <em>link</em></a>
    <p>Some long paragraph
    with a <a href="http://example.com/?my=1">link</a></p>
</div>
Some text in the ending.
HTML;
        //@finishSkipCommitHooks

        $result = $test->collectTexts($html);

        //test return in collectTexts() method
        $this->assertEquals($result, $test->getTexts());

        $cnt = 0;
        $expected = array(
            'text_' . ++$cnt => 'Some text in the beginning.',
            'text_' . ++$cnt => 'Some Title',
            'text_' . ++$cnt => 'Span Text',
            'text_' . ++$cnt => 'My pretty nice <em>link</em>',
            'text_' . ++$cnt => 'Some long paragraph
    with a <a href="http://example.com/?my=1">link</a>',
            'text_' . ++$cnt => 'Some text in the ending.',
        );
        $this->assertEquals($expected, $result);

        //Check output HTML
        //@startSkipCommitHooks
        $expected = <<<HTML
{{cms_text key="text_1"}}
<div class="some-class" style="background-color: #000055">
    <h1>{{cms_text key="text_2"}}</h1>
    <span>{{cms_text key="text_3"}}</span>
    <a href="#mylink">{{cms_text key="text_4"}}</a>
    <p>{{cms_text key="text_5"}}</p>
</div>
{{cms_text key="text_6"}}
HTML;
        //@finishSkipCommitHooks

        $result = $test->getOutputHtml();
        $this->assertEquals($expected, $result);
    }
}

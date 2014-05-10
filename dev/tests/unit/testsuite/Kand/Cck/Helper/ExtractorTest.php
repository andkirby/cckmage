<?php
/**
 * Test of class Kand_Cck_Helper_Data
 *
 * @category    Kand
 * @package     Kand_Cck
 */
class Kand_Cck_Helper_ExtractorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test class name
     *
     * @var string
     */
    protected $_className = 'Kand_Cck_Helper_Extractor';

    /**
     * Output HTML fixture
     *
     * @var string
     */
    protected $_inputHtmlFixture = '_fixture/input.html';

    /**
     * Output HTML fixture
     *
     * @var string
     */
    protected $_outputHtmlFixture = '_fixture/output.html';

    /**
     * Get input HTML
     *
     * @return string
     */
    protected function _getFixtureInputHtml()
    {
        return file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . $this->_inputHtmlFixture);
    }

    /**
     * Get output HTML
     *
     * @return string
     */
    protected function _getFixtureOutputHtml()
    {
        return file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . $this->_outputHtmlFixture);
    }

    /**
     * Get mock object
     *
     * @param array $methods
     * @return Kand_Cck_Helper_Extractor|PHPUnit_Framework_MockObject_MockObject
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

        $html = $this->_getFixtureInputHtml();
        $result = $test->collectTexts($html);

        //test return in collectTexts() method
        $this->assertEquals($result, $test->getTexts());

        $cnt = 0;
        $expected = array(
            'text_' . ++$cnt => 'Some text in the beginning.',
            'text_' . ++$cnt => 'Some Title',
            'text_' . ++$cnt => 'Span Text',
            'text_' . ++$cnt => '/skin/frontend/enterprise/hastens/images/tmp/layering-test.jpg',
            'text_' . ++$cnt => '#mylink',
            'text_' . ++$cnt => 'My pretty nice <em>link</em>',
            'text_' . ++$cnt => 'http://example.com/?my=1',
            'text_' . ++$cnt => 'Some long paragraph
        with a <a href="{{cms_text key=text_7}}">link</a>',
            'text_' . ++$cnt => 'Some text in the ending.',
        );
        $this->assertEquals($expected, $result);

        //Check output HTML
        $expected = $this->_getFixtureOutputHtml();
        $result = $test->getOutputHtml();
        $this->assertEquals($expected, $result);
    }
}

<?php
/**
 * Class Kand_Cck_Helper_Data
 */
class Kand_Cck_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**#@+
     * Tag endings
     */
    const DIRECTIVE_TAG_START       = '__TAG_START__';
    const DIRECTIVE_TAG_END         = '__TAG_END__';
    const DIRECTIVE_TAG_CLOSE_START = '__TAG_CLOSE_START__';
    const DIRECTIVE_TAG_CLOSE_END   = '__TAG_CLOSE_END__';
    const DIRECTIVE_CMS_TAG_START   = '__CMS_TAG_START__';
    const DIRECTIVE_CMS_TAG_END     = '__CMS_TAG_END__';
    /**#@-*/

    /**
     * Unpaired tags list
     *
     * @var array
     */
    protected $_unpairedTags = array(
        'area',
        'base',
        'basefont',
        'bgsound',
        'br',
        'col',
        'command',
        'embed',
        'hr',
        'img',
        'input',
        'isindex',
        'keygen',
        'link',
        'meta',
        'param',
        'source',
        'track',
        'wbr',
    );

    /**
     * Tag endings directives list
     *
     * @var array
     */
    protected $_tagEndings = array(
        self::DIRECTIVE_TAG_START,
        self::DIRECTIVE_TAG_END,
        self::DIRECTIVE_TAG_CLOSE_START,
        self::DIRECTIVE_TAG_CLOSE_END,
        self::DIRECTIVE_CMS_TAG_START,
        self::DIRECTIVE_CMS_TAG_END,
    );

    /**
     * Parsed HTML nodes
     *
     * @var array
     */
    protected $_htmlNodes = array();

    /**
     * Text entities
     *
     * @var array
     */
    protected $_texts = array();

    /**
     * Output HTML with directives for text entities
     *
     * @var array
     */
    protected $_outputHtml = array();

    /**
     * Get text entities
     *
     * @return array
     */
    public function getTexts()
    {
        return $this->_texts;
    }

    /**
     * Get output (parsed) HTML
     *
     * @return array
     */
    public function getOutputHtml()
    {
        return $this->_outputHtml;
    }

    /**
     * Collect text strings from HTML
     *
     * @param string $html
     * @return array
     * @throws Exception When parameter is not string
     */
    public function collectTexts($html)
    {
        if (empty($html)) {
            return array();
        }
        if (!is_string($html)) {
            throw new Exception('HTML parameter must be a string.');
        }
        $this->_htmlNodes = $this->_explodeHtmlToTagNodes($html);

        reset($this->_htmlNodes);
        $nodes             = $this->_makeNodesStructure();
        $this->_outputHtml = $this->_processStructuredTags($nodes);
        return $this->_texts;
    }

    /**
     * Explode HTML string into array with tags
     *
     * @param string $html
     * @return array
     */
    protected function _explodeHtmlToTagNodes($html)
    {
        $html = preg_replace(
            '/(<)([A-z0-9_]+[^>]*)(>)/',
            self::DIRECTIVE_TAG_START . '$2' . self::DIRECTIVE_TAG_END,
            $html
        );
        $html = preg_replace(
            '/(<\/)([A-z0-9_]+[^>]*)(>)/',
            self::DIRECTIVE_TAG_CLOSE_START . '$2' . self::DIRECTIVE_TAG_CLOSE_END,
            $html
        );
        $html = preg_replace(
            '/({{)([A-z0-9_]+[^>]*)(}})/',
            self::DIRECTIVE_CMS_TAG_START . '$2' . self::DIRECTIVE_CMS_TAG_END,
            $html
        );

        foreach ($this->_tagEndings as $directive) {
            $html = $this->_explodeByDirective($html, $directive);
        }
        return $html;
    }

    /**
     * Process structured tags
     *
     * @param array $nodes
     * @param bool  $asText
     * @return string
     * @throws Exception Throw exception when unknown node type
     */
    protected function _processStructuredTags(array $nodes, $asText = false)
    {
        $html = '';
        foreach ($nodes as $node) {
            if ($node['type'] === 'tag') {
                $html .= $this->_getTagHtml($node, $asText);
            } elseif ($node['type'] === 'gap') {
                $html .= $this->_getGapHtml($node);
            } elseif ($node['type'] === 'text') {
                $html .= $this->_getTextHtml($node, $asText);
            } elseif ($node['type'] === 'cms_directive') {
                $html .= $this->_getCmsDirectiveHtml($node, $asText);
            } else {
                throw new Exception('Unknown node type: "' . $node['type'] . '".');
            }
        }
        return $html;
    }

    /**
     * Explode HTML by directive
     *
     * @param string $html
     * @param string $directive
     * @return array
     */
    protected function _explodeByDirective($html, $directive)
    {
        if (is_string($html)) {
            $html = array($html);
        }
        $nodes = array();
        foreach ($html as $item) {
            $result = explode($directive, $item);
            foreach ($result as $itemResult) {
                if ($itemResult) {
                    $nodes[] = $itemResult;
                }
                $nodes[] = $directive;
            }
            array_pop($nodes); //remove redundant node
        }
        return $nodes;
    }

    /**
     * Get tag name from tag body
     *
     * @param string $str
     * @return string
     */
    protected function _getTagName($str)
    {
        preg_match('/^[A-z0-9_]+/', $str, $m);
        return (string)$m[0];
    }

    /**
     * Structure single tag
     *
     * @return array
     * @throws Exception
     */
    protected function _structureTag()
    {
        $node = array();
        $item = current($this->_htmlNodes);
        if ($item === self::DIRECTIVE_TAG_START) {
            $item = $this->_nextHtml(); //tag name
            $node = $this->_getTagElement($item);
            $this->_nextHtml(); //end tag

            if (!$this->_isTagUnpaired($node['name'])) {
                $this->_nextHtml(); //next node
                $node['children'] = $this->_makeNodesStructure($node['name']);
                $node['has_text'] = $this->_isChildrenHasText($node);
            }
        }
        if (!$node) {
            throw new Exception('No node found.');
        }
        return $node;
    }

    /**
     * Structure single CMS directive
     *
     * @return array
     * @throws Exception
     */
    protected function _structureCmsDirective()
    {
        $node = array();
        $item = current($this->_htmlNodes);
        if ($item === self::DIRECTIVE_CMS_TAG_START) {
            $item = $this->_nextHtml(); //tag body
            $node = $this->_getCmsDirectiveElement($item);
            $this->_nextHtml(); //end tag
        }

        if (!$node) {
            throw new Exception('No node found.');
        }
        return $node;
    }

    /**
     * Make nodes tree structure
     *
     * @param string|null $closeTagName
     * @return array
     * @throws Exception
     */
    protected function _makeNodesStructure($closeTagName = null)
    {
        $nodes = array();
        do {
            $item = current($this->_htmlNodes);
            while (!trim($item)) {
                //add gaps
                $nodes[] = $this->_getGapElement($item);
                $item = $this->_nextHtml();
            }
            if (!in_array($item, $this->_tagEndings)) {
                $nodes = array_merge($nodes, $this->_getTextElement($item));
            } elseif ($item === self::DIRECTIVE_TAG_START) {
                $nodes[] = $this->_structureTag();
            } elseif ($item === self::DIRECTIVE_CMS_TAG_START) {
                $nodes[] = $this->_structureCmsDirective();
            } elseif ($this->_isNodeForCloseTag($closeTagName)) {
                //expected tag closed
                $this->_nextHtml(); //skip close tag name
                $this->_nextHtml(); //skip end close tag
                return $nodes;
            } else {
                throw new Exception('Error in HTML nodes on key: ' . key($this->_htmlNodes));
            }
        } while ($this->_nextHtml());

        if ($closeTagName) {
            throw new Exception('Closed tag not found.');
        }

        return $nodes;
    }

    /**
     * Move point of HTML node to the next
     *
     * Returns next element value.
     * Returns FALSE if next element does not exist.
     *
     * @return string|bool
     */
    protected function _nextHtml()
    {
        return next($this->_htmlNodes);
    }

    /**
     * Add text node
     *
     * @param string $text
     * @param string $textLabel
     * @return string
     */
    protected function _addText($text, $textLabel = '')
    {
        $textLabel                = $textLabel ? : 'text_' . (count($this->_texts) + 1);
        $this->_texts[$textLabel] = $text;
        return $textLabel;
    }

    /**
     * Get CMS directive
     *
     * @param string $textLabel
     * @return string
     */
    protected function _getCckCmsDirective($textLabel)
    {
        return "{{cms_text key=\"$textLabel\"}}";
    }

    /**
     * Is node for close tag?
     *
     * @param string $closeTagName
     * @return bool
     */
    protected function _isNodeForCloseTag($closeTagName)
    {
        $result = $closeTagName && current($this->_htmlNodes) === self::DIRECTIVE_TAG_CLOSE_START
            && $this->_nextHtml() === $closeTagName;
        prev($this->_htmlNodes); //rollback next
        return $result;
    }

    /**
     * Get gap element
     *
     * @param string $item
     * @return array
     */
    protected function _getGapElement($item)
    {
        return array(
            'type' => 'gap',
            'body' => $item,
        );
    }

    /**
     * Get tag element
     *
     * @param string $item
     * @return array
     */
    protected function _getTagElement($item)
    {
        return array(
            'type'     => 'tag',
            'has_text' => false,
            'name'     => $this->_getTagName($item),
            'body'     => $item,
            'children' => array(),
        );
    }

    /**
     * Get CMS directive element
     *
     * @param string $item
     * @return array
     */
    protected function _getCmsDirectiveElement($item)
    {
        return array(
            'type'     => 'cms_directive',
            'body'     => $item,
        );
    }

    /**
     * Get text element
     *
     * @param string $text
     * @return array
     */
    protected function _getTextElement($text)
    {
        $nodes = array();
        //find gaps in text element
        $gaps = explode(trim($text), $text);
        if ($gaps[0]) {
            //add found gap
            $nodes[] = $this->_getGapElement($gaps[0]);
        }
        //add text element
        $nodes[] = array(
            'type' => 'text',
            'body' => trim($text),
        );
        if ($gaps[1]) {
            //add found gap
            $nodes[] = $this->_getGapElement($gaps[1]);
        }
        return $nodes;
    }

    /**
     * Check children have at least one text item
     *
     * @param array $node
     * @return bool
     */
    protected function _isChildrenHasText(array $node)
    {
        foreach ($node['children'] as $child) {
            if ($child['type'] === 'text') {
                return true;
            }
        }
        return false;
    }

    /**
     * Get tag HTML
     *
     * @param array $node
     * @param bool $asText
     * @return array
     * @throws Exception
     */
    protected function _getTagHtml(array $node, $asText)
    {
        if ($this->_isTagUnpaired($node['name'])) {
            return "<{$node['body']}>";
        }

        $html = "<{$node['body']}>";
        if ($node['children']) {
            if ($node['has_text'] || $asText) {
                $text = $this->_processStructuredTags($node['children'], true);
                if ($asText) {
                    $html .= $text;
                } else {
                    $textLabel = $this->_addText($text);
                    $html .= $this->_getCckCmsDirective($textLabel);
                }
            } else {
                $html .= $this->_processStructuredTags($node['children'], false);
            }
        }
        $html .= "</{$node['name']}>";
        return $html;
    }

    /**
     * Get text HTML
     *
     * @param array $node
     * @param bool $asText
     * @return string
     */
    protected function _getTextHtml(array $node, $asText)
    {
        if ($asText) {
            return $node['body'];
        } else {
            $textLabel = $this->_addText($node['body']);
            return $this->_getCckCmsDirective($textLabel);
        }
    }

    /**
     * Get CMS directive HTML
     *
     * @param array $node
     * @return string
     */
    protected function _getCmsDirectiveHtml(array $node)
    {
        return '{{' . $node['body'] . '}}';
    }

    /**
     * Get gap HTML
     *
     * @param array $node
     * @return string
     */
    protected function _getGapHtml(array $node)
    {
        return $node['body'];
    }

    /**
     * Check is tag unpaired
     *
     * @param $tag
     * @return bool
     */
    protected function _isTagUnpaired($tag)
    {
        return (bool)in_array(strtolower($tag), $this->_unpairedTags);
    }
}

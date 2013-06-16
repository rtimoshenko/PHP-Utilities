<?php

/*
    Author: Ronald Timoshenko | ronaldtimoshenko.com
    Date: 2013-06-09
*/

class ArrayToFeedWriter
{
    const KEY_NODES = 'nodes';
    const KEY_ATTRIBUTES = 'attributes';
    const KEY_CONTENT = 'content';
    const KEY_NAME = 'name';

    private $_writer = null;
    private $_feedData = null;
    
    // Dependency injected constructor
    public function __construct(XMLWriter $writer, array $feedData)
    {
        $this->_writer = $writer;
        $this->_feedData = $feedData;
    }

    /**
    * Runs the writer
    */
    public function write()
    {
        $writer = $this->_writer;
        $feedData = $this->_feedData;
        $rootNode = current($feedData);
        
        // Fluid interface
        $this->startWriter($writer)
             ->addElementToWriter($rootNode, $writer)
             ->endWriter($writer);
    }
    
    /**
    * Starts $writer XMLWriter and begins ouput to the php stream
    */
    private function startWriter(XMLWriter $writer)
    {
        $writer->openURI('php://output');
        $writer->startDocument('1.0','UTF-8');
        $writer->setIndent(true);
        
        return $this;
    }
    
    /**
    * Closes $writer XMLWriter and flushes it
    */
    private function endWriter(XMLWriter $writer)
    {
        $writer->endDocument();   
        $writer->flush();
    }
    
    /**
    * Takes associative $node array and adds it to the passed in $writer XMLWriter
    */
    private function addElementToWriter(array $node, XMLWriter $writer)
    {
        $hasAttributes = $this->keyHasValue(self::KEY_ATTRIBUTES, $node);
        $hasNodes = $this->keyHasValue(self::KEY_NODES, $node);
        $hasContent = $this->keyHasValue(self::KEY_CONTENT, $node);
        
        $nodeName = $node[self::KEY_NAME];
        $nodeContent = $hasContent ? $node[self::KEY_CONTENT] : null;
        $nodeAttributes = $hasAttributes ? $node[self::KEY_ATTRIBUTES] : null;
        
        if (!$hasNodes && !$hasAttributes)
        {
            $writer->writeElement($nodeName, $nodeContent);
        }
        else
        {
            $writer->startElement($nodeName);
            
            if ($hasAttributes)
            {
                $this->addAttributesToWriter($nodeAttributes, $writer);
            }
            
            if ($hasContent)
            {
                $writer->text($nodeContent);
            }
            
            if ($hasNodes)
            {
                foreach($node[self::KEY_NODES] as $childNode)
                {
                    $this->addElementToWriter($childNode, $writer);
                }
            }
            
            $writer->endElement();
        }
        
        return $this;
    }
    
    /**
    * Takes $attributes array key value pairs and adds them to the passed in $writer XMLWriter
    */
    private function addAttributesToWriter(array $attributes, XMLWriter $writer)
    {
        foreach($attributes as $attrKey => $attrVal)
        {
            $writer->writeAttribute($attrKey, $attrVal);
        }
        
        return $writer;
    }
    
    /**
    * Verifies that the passed in key $key exists and is defined in the passed $sourceArray array
    */
    private function keyHasValue($key, array $sourceArray)
    {        
        return empty($sourceArray) ? false : (isset($sourceArray[$key]) && !empty($sourceArray[$key]));
    }
}




/*
======================================
    SAMPLE USAGE
======================================
*/


// SAMPLE DATA
// Passed in array should use constant values for keys
$sampleData = array(
    array(
        'name'            => 'productlist',
        'attributes'    => array(
            'retailer'    => 'www.example.com'
        ),
        'nodes'            => array(
            array(
                'name'            => 'product',
                'attributes'    => array(
                    'type'    => 'product type value'
                ),
                'nodes'            => array(
                    array(
                        'name'        => 'manufacturer',
                        'content'    => 'manufacturer value'
                    ),
                    array(
                        'name'        => 'description',
                        'content'    => 'Lorem ipsum & dolor consectetuer adipiscing elit'
                    )
                )
            ),
            array(
                'name'            => 'product',
                'attributes'    => array(
                    'type'    => 'product type value'
                ),
                'nodes'            => array(
                    array(
                        'name'        => 'manufacturer',
                        'content'    => 'manufacturer value'
                    ),
                    array(
                        'name'        => 'description',
                        'content'    => 'Lorem ipsum & dolor consectetuer adipiscing elit'
                    )
                )
            )
        )
    )
);

$feedWriter = new ArrayToFeedWriter(new XMLWriter(), $sampleData);
$feedWriter->write();

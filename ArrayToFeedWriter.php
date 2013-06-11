<?php

/*
	Author: Ronald Timoshenko | ronaldtimoshenko.com
	Date: 2013-06-09
*/

class ArrayToFeedWriter
{
    private $writer = null;
    private $feedData = null;
    
    public function __construct(XMLWriter $writer, array $feedData)
    {
        $this->writer = $writer;
        $this->feedData = $feedData;
    }

    /**
    * Runs the writer
    */
    public function write()
    {
    	$writer = $this->writer;
    	$feedData = $this->feedData;
    	
    	$rootNode = current($feedData);
    	
        $this->startWriter($writer);
        $this->addElementToWriter($writer, $rootNode);
        $this->endWriter($writer);
    }
    
    /**
    * Starts $writer XMLWriter and begins ouput to the php stream
    */
    private function startWriter(XMLWriter $writer)
    {
		$writer->openURI('php://output');
		$writer->startDocument('1.0','UTF-8');
		$writer->setIndent(true);
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
    private function addElementToWriter(XMLWriter $writer, array $node)
    {
    	// TODO: Convert key names to public constants
    	$hasAttributes = isset($node['attributes']) && !empty($node['attributes']);
    	$hasNodes = isset($node['nodes']) && !empty($node['nodes']);
    	$hasContent = isset($node['content']) && !empty($node['content']);
    	
    	$nodeName = $node['name'];
    	$nodeContent = $hasContent ? $node['content'] : null;
    	$nodeAttributes = $hasAttributes ? $node['attributes'] : null;
	    
	    if (!$hasNodes && !$hasAttributes)
	    {
			$writer->writeElement($nodeName, $nodeContent);
	    }
	    else
	    {
	    	$writer->startElement($nodeName);
	    	
		    if ($hasAttributes)
		    {
			    $this->addAttributesToWriter($writer, $nodeAttributes);
		    }
		    
		    if ($hasContent)
		    {
			    $writer->text($nodeContent);
		    }
		    
		    if ($hasNodes)
		    {
			    foreach($node['nodes'] as $childNode)
			    {
				    $this->addElementToWriter($writer, $childNode);
			    }
		    }
		    
		    $writer->endElement();
	    }
    }
    
    /**
    * Takes $attributes array key value pairs and adds them to the passed in $writer XMLWriter
    */
    private function addAttributesToWriter(XMLWriter $writer, array $attributes)
    {
	    foreach($attributes as $attrKey => $attrVal)
	    {
		    $writer->writeAttribute($attrKey, $attrVal);
	    }
    }
}

$sampleData = array(
	array(
		'name'			=> 'productlist',
		'attributes'	=> array(
			'retailer'	=> 'www.example.com'
		),
		'nodes'			=> array(
			array(
				'name'			=> 'product',
				'attributes'	=> array(
					'type'	=> 'product type value'
				),
				'nodes'			=> array(
					array(
						'name'		=> 'manufacturer',
						'content'	=> 'manufacturer value'
					),
					array(
						'name'		=> 'description',
						'content'	=> 'Lorem ipsum & dolor consectetuer adipiscing elit'
					)
				)
			),
			array(
				'name'			=> 'product',
				'attributes'	=> array(
					'type'	=> 'product type value'
				),
				'nodes'			=> array(
					array(
						'name'		=> 'manufacturer',
						'content'	=> 'manufacturer value'
					),
					array(
						'name'		=> 'description',
						'content'	=> 'Lorem ipsum & dolor consectetuer adipiscing elit'
					)
				)
			)
		)
	)
);

$feedWriter = new ArrayToFeedWriter(new XMLWriter(), $sampleData);
$feedWriter->write();

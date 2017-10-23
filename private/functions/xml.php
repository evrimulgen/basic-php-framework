<?php

function DOMNode_getChildElements($node, $tagname=null)
{
	$childElements = [];
	for($i=0; $i<$node->childNodes->length; $i++)
	{
		$childNode = $node->childNodes->item($i);
		if($childNode->nodeType == XML_ELEMENT_NODE)
		{
			if($tagname==null)
			{
				array_push($childElements, $childNode);
			}
			else if($tagname==$childNode->nodeName)
			{
				array_push($childElements, $childNode);
			}
		}
	}
	return $childElements;
}

function DOMNode_getFirstChildElement($node, $tagname=null)
{
	for($i=0; $i<$node->childNodes->length; $i++)
	{
		$childNode = $node->childNodes->item($i);
		if($childNode->nodeType == XML_ELEMENT_NODE)
		{
			if($tagname==null)
			{
				return $childNode;
			}
			else if($tagname==$childNode->nodeName)
			{
				return $childNode;
			}
		}
	}
	return null;
}

function DOMNode_getElementsInPath($node, $path)
{
	if(!is_array($path))
	{
		return [];
	}
	if(count($path)==0)
	{
		return [];
	}
	$lastIndex = count($path)-1;
	$i = 0;
	foreach($path as $pathPart)
	{
		if($i==$lastIndex)
		{
			error_log("finding children with tag ".$pathPart);
			return DOMNode_getChildElements($node, $pathPart);
		}
		else
		{
			$node = DOMNode_getFirstChildElement($node, $pathPart);
			if($node===null)
			{
				error_log("couldn't find element with tag ".$pathPart);
				return [];
			}
			error_log("found node with tag ".$pathPart);
		}
		$i++;
	}
	//this line will never be hit, but it doesn't hurt to put this here anyways
	return [];
}

?>

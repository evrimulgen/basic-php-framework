<?php

function DOMNode_getChildElements($node, $tagname=null)
{
	$childElements = [];
	foreach($node->childNodes as $childNode)
	{
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
	foreach($node->childNodes as $childNode)
	{
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
			return DOMNode_getChildElements($node, $pathPart);
		}
		else
		{
			$node = DOMNode_getFirstChildElement($node, $pathPart);
			if($node===null)
			{
				return [];
			}
		}
		$i++;
	}
	//this line will never be hit, but it doesn't hurt to put this here anyways
	return [];
}

?>

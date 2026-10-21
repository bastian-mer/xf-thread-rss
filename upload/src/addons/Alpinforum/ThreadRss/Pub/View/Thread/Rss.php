<?php

namespace Alpinforum\ThreadRss\Pub\View\Thread;

class Rss extends \XF\Mvc\View
{
	const NS_XMLNS = 'http://www.w3.org/2000/xmlns/';
	const NS_ATOM = 'http://www.w3.org/2005/Atom';
	const NS_DC = 'http://purl.org/dc/elements/1.1/';
	const NS_CONTENT = 'http://purl.org/rss/1.0/modules/content/';

	public function renderRss()
	{
		$app = \XF::app();
		$router = $app->router('public');
		$formatter = $app->stringFormatter();

		/** @var \XF\Entity\Thread $thread */
		$thread = $this->params['thread'];
		$posts = $this->params['posts'];

		$threadTitle = $formatter->censorText($thread->title);
		$boardTitle = (string)$app->options()->boardTitle;
		$threadUrl = $router->buildLink('canonical:threads', $thread);
		$feedUrl = $router->buildLink('canonical:threads/index.rss', $thread);

		$lastBuild = $thread->last_post_date ?: \XF::$time;

		$doc = new \DOMDocument('1.0', 'UTF-8');
		$doc->formatOutput = true;

		$rss = $doc->createElement('rss');
		$rss->setAttribute('version', '2.0');
		$rss->setAttributeNS(self::NS_XMLNS, 'xmlns:atom', self::NS_ATOM);
		$rss->setAttributeNS(self::NS_XMLNS, 'xmlns:dc', self::NS_DC);
		$rss->setAttributeNS(self::NS_XMLNS, 'xmlns:content', self::NS_CONTENT);
		$doc->appendChild($rss);

		$channel = $doc->createElement('channel');
		$rss->appendChild($channel);

		$this->addText($doc, $channel, 'title', $threadTitle . ($boardTitle !== '' ? ' | ' . $boardTitle : ''));
		$this->addText($doc, $channel, 'link', $threadUrl);
		$this->addText($doc, $channel, 'description', \XF::phrase('alpinforum_threadrss_feed_description', [
			'title' => $threadTitle,
			'board' => $boardTitle,
		])->render('raw'));
		$this->addText($doc, $channel, 'language', (string)\XF::language()->getLanguageCode());
		$this->addText($doc, $channel, 'lastBuildDate', $this->rssDate($lastBuild));
		$this->addText($doc, $channel, 'generator', 'XenForo / Alpinforum Thread RSS');

		$atomLink = $doc->createElementNS(self::NS_ATOM, 'atom:link');
		$atomLink->setAttribute('href', $feedUrl);
		$atomLink->setAttribute('rel', 'self');
		$atomLink->setAttribute('type', 'application/rss+xml');
		$channel->appendChild($atomLink);

		foreach ($posts AS $post)
		{
			/** @var \XF\Entity\Post $post */
			$item = $doc->createElement('item');
			$channel->appendChild($item);

			$postUrl = $router->buildLink('canonical:posts', $post);
			$username = (string)$post->username;

			$this->addText($doc, $item, 'title', \XF::phrase('alpinforum_threadrss_item_title', [
				'number' => $post->position + 1,
				'username' => $username,
				'title' => $threadTitle,
			])->render('raw'));
			$this->addText($doc, $item, 'link', $postUrl);

			$guid = $this->addText($doc, $item, 'guid', $postUrl);
			$guid->setAttribute('isPermaLink', 'true');

			$this->addText($doc, $item, 'pubDate', $this->rssDate($post->post_date));
			$this->addText($doc, $item, 'dc:creator', $username, self::NS_DC);

			$plain = $formatter->censorText($formatter->stripBbCode($post->message, ['stripQuote' => true]));
			$this->addText($doc, $item, 'description', $formatter->snippetString($plain, 300));

			$this->addText($doc, $item, 'content:encoded', $this->renderPostHtml($post, $plain), self::NS_CONTENT);
		}

		return $doc->saveXML();
	}

	protected function renderPostHtml(\XF\Entity\Post $post, $fallbackPlain)
	{
		try
		{
			$options = method_exists($post, 'getBbCodeRenderOptions')
				? $post->getBbCodeRenderOptions('post', 'emailHtml')
				: [];

			$html = \XF::app()->bbCode()->render($post->message, 'emailHtml', 'post', $post, $options);
			if (is_string($html) && $html !== '')
			{
				return $html;
			}
		}
		catch (\Throwable $e)
		{
			\XF::logException($e, false, 'Thread RSS: ');
		}

		return nl2br(htmlspecialchars((string)$fallbackPlain, ENT_QUOTES, 'UTF-8'));
	}

	protected function addText(\DOMDocument $doc, \DOMElement $parent, $name, $value, $namespace = null)
	{
		$element = $namespace
			? $doc->createElementNS($namespace, $name)
			: $doc->createElement($name);

		$element->appendChild($doc->createTextNode($this->cleanXml((string)$value)));
		$parent->appendChild($element);

		return $element;
	}

	protected function cleanXml($string)
	{
		$clean = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $string);
		return $clean === null ? '' : $clean;
	}

	protected function rssDate($timestamp)
	{
		return gmdate('D, d M Y H:i:s', (int)$timestamp) . ' +0000';
	}
}

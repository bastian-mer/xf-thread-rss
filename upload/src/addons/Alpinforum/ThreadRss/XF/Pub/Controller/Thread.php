<?php

namespace Alpinforum\ThreadRss\XF\Pub\Controller;

use XF\Mvc\ParameterBag;

class Thread extends XFCP_Thread
{
	/**
	 * Maximale Anzahl Beiträge im Feed eines Themas.
	 */
	const ALPINFORUM_THREADRSS_LIMIT = 25;

	public function actionIndex(ParameterBag $params)
	{
		if ($this->responseType === 'rss')
		{
			return $this->alpinforumThreadRss($params);
		}

		return parent::actionIndex($params);
	}

	protected function alpinforumThreadRss(ParameterBag $params)
	{
		/** @var \XF\Entity\Thread $thread */
		$thread = $this->assertViewableThread($params->thread_id, ['Forum', 'Forum.Node']);

		$limit = static::ALPINFORUM_THREADRSS_LIMIT;

		$posts = $this->finder('XF:Post')
			->where('thread_id', $thread->thread_id)
			->where('message_state', 'visible')
			->with('User')
			->order('post_date', 'DESC')
			->limit($limit * 2)
			->fetch();

		foreach ($posts AS $post)
		{
			$post->hydrateRelation('Thread', $thread);
		}

		$posts = $posts->filterViewable()->slice(0, $limit);

		return $this->view('Alpinforum\ThreadRss:Thread\Rss', '', [
			'thread' => $thread,
			'posts' => $posts,
		]);
	}
}

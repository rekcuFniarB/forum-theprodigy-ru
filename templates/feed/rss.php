<?= '<?xml version="1.0"?>' ?>

<rss version="2.0">
  <channel>
    <title><?= $this->host ?> &#12299; <?= $this->title ?></title>
    <link><?= $this->main_link ?></link>
    <language>ru</language>
    <pubDate><?= $this->pub_date ?></pubDate>

    <lastBuildDate><?= $this->pub_date ?></lastBuildDate>
    <generator>Forum.theProdigy.ru</generator>
    
    <?php foreach($this->posts as $article): ?>
    <item>
      <title><?= $article['subject'] ?></title>
      <link><?= $this->siteurl ?>/feed/<?= $article['ID_CAT'] ?>/<?= $article['ID_BOARD'] ?>/<?= $article['ID_MSG'] ?>/</link>
      <description><?= $article['annotation'] ?>
      <?= $article['body'] ?>
      </description>
      <pubDate><?= date('r', $article['date']) ?></pubDate>
      <guid><?= $this->siteurl ?>/feed/<?= $article['ID_CAT'] ?>/<?= $article['ID_BOARD'] ?>/<?= $article['ID_MSG'] ?>/#article<?= $article['ID_MSG'] ?></guid>
      <category><?= $article['rss-cat'] ?></category>
    </item>
    <?php endforeach; ?>
  </channel>
</rss>

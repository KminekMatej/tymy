<?php
// source: /home/www/klubpivovarek.cz/www/tymy.klubpivovarek.cz/app/presenters/templates/@layout.latte

use Latte\Runtime as LR;

class Template907f4a18f3 extends Latte\Runtime\Template
{
	public $blocks = [
		'styles' => 'blockStyles',
		'scripts' => 'blockScripts',
		'head' => 'blockHead',
	];

	public $blockTypes = [
		'styles' => 'html',
		'scripts' => 'html',
		'head' => 'html',
	];


	function main()
	{
		extract($this->params);
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">

	<title><?php
		if (isset($this->blockQueue["title"])) {
			$this->renderBlock('title', $this->params, function ($s, $type) {
				$_fi = new LR\FilterInfo($type);
				return LR\Filters::convertTo($_fi, 'html', $this->filters->filterContent('striphtml', $_fi, $s));
			});
			?> | <?php
		}
?>Nette Sandbox</title>

	<meta name="viewport" content="width=device-width, initial-scale=1">
<?php
		if ($this->getParentName()) return get_defined_vars();
		$this->renderBlock('styles', get_defined_vars());
		$this->renderBlock('scripts', get_defined_vars());
		?>	<?php
		$this->renderBlock('head', get_defined_vars());
?>
</head>

<body>
<?php
		$iterations = 0;
		foreach ($flashes as $flash) {
			?>	<div<?php if ($_tmp = array_filter(['flash', $flash->type])) echo ' class="', LR\Filters::escapeHtmlAttr(implode(" ", array_unique($_tmp))), '"' ?>><?php
			echo LR\Filters::escapeHtmlText($flash->message) /* line 30 */ ?></div>
<?php
			$iterations++;
		}
?>

<?php
		$this->renderBlock('content', $this->params, 'html');
?>


</body>
</html>
<?php
		return get_defined_vars();
	}


	function prepare()
	{
		extract($this->params);
		if (isset($this->params['flash'])) trigger_error('Variable $flash overwritten in foreach on line 30');
		Nette\Bridges\ApplicationLatte\UIRuntime::initialize($this, $this->parentName, $this->blocks);
		
	}


	function blockStyles($_args)
	{
		extract($_args);
		?>        <link rel="stylesheet" href="<?php echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 14 */ ?>/css/<?php
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($bootstrap_type)) /* line 14 */ ?>.css">
        <link href="https://fonts.googleapis.com/css?family=Noto+Sans" rel="stylesheet">
        <link rel="stylesheet" href="<?php echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 16 */ ?>/css/style.css">
        
<?php
	}


	function blockScripts($_args)
	{
		extract($_args);
		?>        <script src="<?php echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 20 */ ?>/js/<?php
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($jQuery_type)) /* line 20 */ ?>.js"></script>
	<script src="https://nette.github.io/resources/js/netteForms.min.js"></script>
	<script src="<?php echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 22 */ ?>/js/main.js"></script>
        <script src="<?php echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 23 */ ?>/js/<?php
		echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($bootstrap_type)) /* line 23 */ ?>.js"></script>
        
<?php
	}


	function blockHead($_args)
	{
		
	}

}

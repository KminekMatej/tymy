<?php
// source: /home/www/klubpivovarek.cz/www/tymy.klubpivovarek.cz/app/presenters/templates/Sign/in.latte

use Latte\Runtime as LR;

class Template7283330843 extends Latte\Runtime\Template
{
	public $blocks = [
		'styles' => 'blockStyles',
		'content' => 'blockContent',
	];

	public $blockTypes = [
		'styles' => 'html',
		'content' => 'html',
	];


	function main()
	{
		extract($this->params);
		if ($this->getParentName()) return get_defined_vars();
		$this->renderBlock('styles', get_defined_vars());
?>

<?php
		$this->renderBlock('content', get_defined_vars());
		return get_defined_vars();
	}


	function prepare()
	{
		extract($this->params);
		Nette\Bridges\ApplicationLatte\UIRuntime::initialize($this, $this->parentName, $this->blocks);
		
	}


	function blockStyles($_args)
	{
		extract($_args);
		$this->renderBlockParent('styles', get_defined_vars());
		?><link rel="stylesheet" href="<?php echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 3 */ ?>/css/sign.css">
<?php
	}


	function blockContent($_args)
	{
		extract($_args);
?>
<div class="login">
<?php
		/* line 8 */ $_tmp = $this->global->uiControl->getComponent("signInForm");
		if ($_tmp instanceof Nette\Application\UI\IRenderable) $_tmp->redrawControl(NULL, FALSE);
		$_tmp->render();
?>
    <table class="under">
        <tr>
            <td style="padding-right:5px"><a href="<?php echo LR\Filters::escapeHtmlAttr($this->global->uiPresenter->link("Sign:up")) ?>">Registrovat</a></td>
            <td style="padding-left:5px"><a href="<?php echo LR\Filters::escapeHtmlAttr($this->global->uiPresenter->link("Sign:pwdlost")) ?>">Ztracené heslo</a></td></tr>
    </table>
</div><?php
	}

}

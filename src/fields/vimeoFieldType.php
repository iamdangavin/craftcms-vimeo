<?php

namespace iamdangavin\vimeo\fields;

use Craft;
use craft\base\Field;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use craft\base\SortableFieldInterface;
use craft\helpers\Db;
use craft\helpers\Html;
use craft\helpers\Json;
use craft\web\View;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\db\Schema;

class vimeoFieldType extends Field implements PreviewableFieldInterface, SortableFieldInterface
{
	
	// Properties
	// =========================================================================

	/**
	 * @var string The UI mode of the field.
	 * @since 3.5.0
	 */
	public $uiMode = 'normal';
	public $mode = 'plain';
	public $modeOverride;

	/**
	 * @var string|null The input’s placeholder text
	 */
	public $placeholder;

	/**
	 * @var bool Whether the input should use monospace font
	 */
	public $code = false;

	/**
	 * @var bool Whether the input should allow line breaks
	 */
	public $multiline = false;

	/**
	 * @var int The minimum number of rows the input should have, if multi-line
	 */
	public $initialRows = 4;

	/**
	 * @var int|null The maximum number of characters allowed in the field
	 */
	public $charLimit;

	/**
	 * @var int|null The maximum number of bytes allowed in the field
	 * @since 3.4.0
	 */
	public $byteLimit;

	/**
	 * @var string|null The type of database column the field should have in the content table
	 */
	public $columnType;
	
	public static function displayName(): string
	{
		return Craft::t('vimeo', 'Vimeo');
	}
	
	public function __construct(array $config = [])
	{
		
		if (isset($config['limitUnit'], $config['fieldLimit'])) {
			if ($config['limitUnit'] === 'chars') {
					$config['charLimit'] = (int)$config['fieldLimit'] ?: null;
			} else {
					$config['byteLimit'] = (int)$config['fieldLimit'] ?: null;
			}
			unset($config['limitUnit'], $config['fieldLimit']);
		}
	
		if (isset($config['charLimit']) && empty($config['charLimit'])) {
			unset($config['charLimit']);
		}
	
		if (isset($config['byteLimit']) && empty($config['byteLimit'])) {
			unset($config['byteLimit']);
		}
	
		if (isset($config['columnType']) && $config['columnType'] === 'auto') {
			unset($config['columnType']);
		}
	
		// This existed at one point way back in the day.
		unset($config['maxLengthUnit']);
		
		parent::__construct($config);
	}
	
	public function getSettingsHtml()
	{
		// Render the settings template
		return Craft::$app->getView()->renderTemplate(
			'vimeo/field/settings',
			[
				'field' => $this
			]
		);
	}

	public function getContentColumnType(): string
	{
		return Schema::TYPE_TEXT;
	}

	public function normalizeValue($value, ElementInterface $element = null)
	{

		if (is_string($value) && !empty($value)) {
			$value = Json::decodeIfJson($value);
		}

		return $value !== '' ? $value : null;
	}
	
	public function getInputHtml($value, ElementInterface $element = null): string
	{
		// Get our id and namespace
		if (version_compare(Craft::$app->getVersion(), '3.5', '>=')) {
			$id = Html::id($this->handle);
		} else {
			/** @noinspection PhpDeprecationInspection */
			$id = Craft::$app->getView()->formatInputId($this->handle);
		}

		$namespacedId = Craft::$app->getView()->namespaceInputId($id);

		return Craft::$app->getView()->renderTemplate(
			'vimeo/field/input',
			[
				'name' => $this->handle,
				'value' => $value,
				'field' => $this,
				'id' => $id,
				'namespacedId' => $namespacedId
			]
		);
	}
	
}
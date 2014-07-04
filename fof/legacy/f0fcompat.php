<?php
/**
 * @package        fof
 * @copyright      2014 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license        GNU GPL version 3 or later
 */

/**
 * This is a legacy layer with code written for FOF 2.3 (F0F - F-zero-F prefix). You are advised to NOT use this in
 * production code. This file is like a set of training wheels, to help you migrate your code to FOF 3. Since FOF 3 is
 * not backwards compatible with FOF 2.x, using this code on a production website will cause GRAVE ISSUES with existing
 * software based on FOF 2.x. You Have Been Warned!
 */

if (defined('F0F_INCLUDED'))
{
	return;
}

define('F0F_INCLUDED', '3.999.999_DO_NOT_USE_IN_PRODUCTION');

class_alias('\\FOF30\\Autoloader\\Autoloader', 'F0FAutoloaderFof');
class_alias('\\FOF30\\Config\\Domain\\Dispatcher', 'F0FConfigDomainDispatcher');
class_alias('\\FOF30\\Config\\Domain\\Domain', 'F0FConfigDomainInterface');
class_alias('\\FOF30\\Config\\Domain\\Tables', 'F0FConfigDomainTables');
class_alias('\\FOF30\\Config\\Domain\\Views', 'F0FConfigDomainViews');
class_alias('\\FOF30\\Config\\Provider', 'F0FConfigProvider');
class_alias('\\FOF30\\Controller\\Controller', 'F0FController');
class_alias('\\FOF30\\Database\\Installer', 'F0FDatabaseInstaller');
class_alias('\\FOF30\\Database\\DatabaseIterator', 'F0FDatabaseIterator');
class_alias('\\FOF30\\Database\\Iterator\\Azure', 'F0FDatabaseIteratorAzure');
class_alias('\\FOF30\\Database\\Iterator\\Mysql', 'F0FDatabaseIteratorMysql');
class_alias('\\FOF30\\Database\\Iterator\\Mysqli', 'F0FDatabaseIteratorMysqli');
class_alias('\\FOF30\\Database\\Iterator\\Pdo', 'F0FDatabaseIteratorPdo');
class_alias('\\FOF30\\Database\\Iterator\\Postgresql', 'F0FDatabaseIteratorPostgresql');
class_alias('\\FOF30\\Database\\Iterator\\Sqlsrv', 'F0FDatabaseIteratorSqlsrv');
class_alias('\\FOF30\\Dispatcher\\Dispatcher', 'F0FDispatcher');
class_alias('\\FOF30\\Encrypt\\Aes', 'F0FEncryptAes');
class_alias('\\FOF30\\Encrypt\\Base32', 'F0FEncryptBase32');
class_alias('\\FOF30\\Encrypt\\Totp', 'F0FEncryptTotp');
class_alias('\\FOF30\\Form\\Form', 'F0FForm');
class_alias('\\FOF30\\Form\\Field', 'F0FFormField');
class_alias('\\FOF30\\Form\\Field\\Accesslevel', 'F0FFormFieldAccesslevel');
class_alias('\\FOF30\\Form\\Field\\Actions', 'F0FFormFieldActions');
class_alias('\\FOF30\\Form\\Field\\Button', 'F0FFormFieldButton');
class_alias('\\FOF30\\Form\\Field\\Cachehandler', 'F0FFormFieldCachehandler');
class_alias('\\FOF30\\Form\\Field\\Calendar', 'F0FFormFieldCalendar');
class_alias('\\FOF30\\Form\\Field\\Captcha', 'F0FFormFieldCaptcha');
class_alias('\\FOF30\\Form\\Field\\Checkbox', 'F0FFormFieldCheckbox');
class_alias('\\FOF30\\Form\\Field\\Components', 'F0FFormFieldComponents');
class_alias('\\FOF30\\Form\\Field\\Editor', 'F0FFormFieldEditor');
class_alias('\\FOF30\\Form\\Field\\Email', 'F0FFormFieldEmail');
class_alias('\\FOF30\\Form\\Field\\Groupedlist', 'F0FFormFieldGroupedlist');
class_alias('\\FOF30\\Form\\Field\\Hidden', 'F0FFormFieldHidden');
class_alias('\\FOF30\\Form\\Field\\Image', 'F0FFormFieldImage');
class_alias('\\FOF30\\Form\\Field\\Imagelist', 'F0FFormFieldImagelist');
class_alias('\\FOF30\\Form\\Field\\Integer', 'F0FFormFieldInteger');
class_alias('\\FOF30\\Form\\Field\\Language', 'F0FFormFieldLanguage');
class_alias('\\FOF30\\Form\\Field\\Select', 'F0FFormFieldList');
class_alias('\\FOF30\\Form\\Field\\Media', 'F0FFormFieldMedia');
class_alias('\\FOF30\\Form\\Field\\Model', 'F0FFormFieldModel');
class_alias('\\FOF30\\Form\\Field\\Ordering', 'F0FFormFieldOrdering');
class_alias('\\FOF30\\Form\\Field\\Password', 'F0FFormFieldPassword');
class_alias('\\FOF30\\Form\\Field\\Plugins', 'F0FFormFieldPlugins');
class_alias('\\FOF30\\Form\\Field\\Published', 'F0FFormFieldPublished');
class_alias('\\FOF30\\Form\\Field\\Radio', 'F0FFormFieldRadio');
class_alias('\\FOF30\\Form\\Field\\Rules', 'F0FFormFieldRules');
class_alias('\\FOF30\\Form\\Field\\Selectrow', 'F0FFormFieldSelectrow');
class_alias('\\FOF30\\Form\\Field\\Sessionhandler', 'F0FFormFieldSessionhandler');
class_alias('\\FOF30\\Form\\Field\\Spacer', 'F0FFormFieldSpacer');
class_alias('\\FOF30\\Form\\Field\\Sql', 'F0FFormFieldSql');
class_alias('\\FOF30\\Form\\Field\\Tag', 'F0FFormFieldTag');
class_alias('\\FOF30\\Form\\Field\\Tel', 'F0FFormFieldTel');
class_alias('\\FOF30\\Form\\Field\\Text', 'F0FFormFieldText');
class_alias('\\FOF30\\Form\\Field\\Textarea', 'F0FFormFieldTextarea');
class_alias('\\FOF30\\Form\\Field\\Timezone', 'F0FFormFieldTimezone');
class_alias('\\FOF30\\Form\\Field\\Title', 'F0FFormFieldTitle');
class_alias('\\FOF30\\Form\\Field\\Url', 'F0FFormFieldUrl');
class_alias('\\FOF30\\Form\\Field\\User', 'F0FFormFieldUser');
class_alias('\\FOF30\\Form\\Field\\Usergroup', 'F0FFormFieldUsergroup');
class_alias('\\FOF30\\Form\\Header', 'F0FFormHeader');
class_alias('\\FOF30\\Form\\Header\\Accesslevel', 'F0FFormHeaderAccesslevel');
class_alias('\\FOF30\\Form\\Header\\Field', 'F0FFormHeaderField');
class_alias('\\FOF30\\Form\\Header\\Fielddate', 'F0FFormHeaderFielddate');
class_alias('\\FOF30\\Form\\Header\\Fieldsearchable', 'F0FFormHeaderFieldsearchable');
class_alias('\\FOF30\\Form\\Header\\Fieldselectable', 'F0FFormHeaderFieldselectable');
class_alias('\\FOF30\\Form\\Header\\Fieldsql', 'F0FFormHeaderFieldsql');
class_alias('\\FOF30\\Form\\Header\\Filterdate', 'F0FFormHeaderFilterdate');
class_alias('\\FOF30\\Form\\Header\\Filtersearchable', 'F0FFormHeaderFiltersearchable');
class_alias('\\FOF30\\Form\\Header\\Filterselectable', 'F0FFormHeaderFilterselectable');
class_alias('\\FOF30\\Form\\Header\\Filtersql', 'F0FFormHeaderFiltersql');
class_alias('\\FOF30\\Form\\Header\\Language', 'F0FFormHeaderLanguage');
class_alias('\\FOF30\\Form\\Header\\Model', 'F0FFormHeaderModel');
class_alias('\\FOF30\\Form\\Header\\Ordering', 'F0FFormHeaderOrdering');
class_alias('\\FOF30\\Form\\Header\\Published', 'F0FFormHeaderPublished');
class_alias('\\FOF30\\Form\\Header\\Rowselect', 'F0FFormHeaderRowselect');
class_alias('\\FOF30\\Form\\Helper', 'F0FFormHelper');
class_alias('\\FOF30\\Hal\\Document', 'F0FHalDocument');
class_alias('\\FOF30\\Hal\\Link', 'F0FHalLink');
class_alias('\\FOF30\\Hal\\Links', 'F0FHalLinks');
class_alias('\\FOF30\\Hal\\Render\\RenderInterface', 'F0FHalRenderInterface');
class_alias('\\FOF30\\Hal\\Render\\Json', 'F0FHalRenderJson');
class_alias('\\FOF30\\Inflector\\Inflector', 'F0FInflector');
class_alias('\\FOF30\\Input\\Input', 'F0FInput');
class_alias('\\FOF30\\Integration\\Joomla\\Filesystem\\Filesystem', 'F0FIntegrationJoomlaFilesystem');
class_alias('\\FOF30\\Integration\\Joomla\\Platform', 'F0FIntegrationJoomlaPlatform');
class_alias('\\FOF30\\Layout\\File', 'F0FLayoutFile');
class_alias('\\FOF30\\Layout\\Helper', 'F0FLayoutHelper');
class_alias('\\FOF30\\Model\\Model', 'F0FModel');
class_alias('\\FOF30\\Model\\Behavior', 'F0FModelBehavior');
class_alias('\\FOF30\\Model\\Behavior\\Access', 'F0FModelBehaviorAccess');
class_alias('\\FOF30\\Model\\Behavior\\Enabled', 'F0FModelBehaviorEnabled');
class_alias('\\FOF30\\Model\\Behavior\\Filters', 'F0FModelBehaviorFilters');
class_alias('\\FOF30\\Model\\Behavior\\Language', 'F0FModelBehaviorLanguage');
class_alias('\\FOF30\\Model\\Behavior\\Mine', 'F0FModelBehaviorPrivate');
class_alias('\\FOF30\\Model\\Dispatcher\\Behavior', 'F0FModelDispatcherBehavior');
class_alias('\\FOF30\\Model\\Field', 'F0FModelField');
class_alias('\\FOF30\\Model\\Field\\Boolean', 'F0FModelFieldBoolean');
class_alias('\\FOF30\\Model\\Field\\Date', 'F0FModelFieldDate');
class_alias('\\FOF30\\Model\\Field\\Number', 'F0FModelFieldNumber');
class_alias('\\FOF30\\Model\\Field\\Text', 'F0FModelFieldText');
class_alias('\\FOF30\\Platform\\Platform', 'F0FPlatform');
class_alias('\\FOF30\\Platform\\Filesystem\\Filesystem', 'F0FPlatformFilesystem');
class_alias('\\FOF30\\Render\\RenderAbstract', 'F0FRenderAbstract');
class_alias('\\FOF30\\Render\\Joomla', 'F0FRenderJoomla');
class_alias('\\FOF30\\Render\\Joomla3', 'F0FRenderJoomla3');
class_alias('\\FOF30\\Render\\Strapper', 'F0FRenderStrapper');
class_alias('\\FOF30\\String\\Utils', 'F0FStringUtils');
class_alias('\\FOF30\\Table\\Table', 'F0FTable');
class_alias('\\FOF30\\Table\\Behavior', 'F0FTableBehavior');
class_alias('\\FOF30\\Table\\Behavior\\Assets', 'F0FTableBehaviorAssets');
class_alias('\\FOF30\\Table\\Behavior\\ContentHistory', 'F0FTableBehaviorContenthistory');
class_alias('\\FOF30\\Table\\Behavior\\Tags', 'F0FTableBehaviorTags');
class_alias('\\FOF30\\Table\\Dispatcher\\Behavior', 'F0FTableDispatcherBehavior');
class_alias('\\FOF30\\Table\\Nested', 'F0FTableNested');
class_alias('\\FOF30\\Table\\Relations', 'F0FTableRelations');
class_alias('\\FOF30\\Template\\Utils', 'F0FTemplateUtils');
class_alias('\\FOF30\\Toolbar\\Toolbar', 'F0FToolbar');
class_alias('\\FOF30\\Utils\\ArrayUtils\\ArrayUtils', 'F0FUtilsArray');
class_alias('\\FOF30\\Utils\\Installscript\\Installscript', 'F0FUtilsInstallscript');
class_alias('\\FOF30\\Utils\\Object\\Object', 'F0FUtilsObject');
class_alias('\\FOF30\\Utils\\Observable\\Dispatcher', 'F0FUtilsObservableDispatcher');
class_alias('\\FOF30\\Utils\\Observable\\Event', 'F0FUtilsObservableEvent');
class_alias('\\FOF30\\Utils\\Update\\Update', 'F0FUtilsUpdate');
class_alias('\\FOF30\\View\\View', 'F0FView');
class_alias('\\FOF30\\View\\Csv', 'F0FViewCsv');
class_alias('\\FOF30\\View\\Form', 'F0FViewForm');
class_alias('\\FOF30\\View\\Html', 'F0FViewHtml');
class_alias('\\FOF30\\View\\Json', 'F0FViewJson');
class_alias('\\FOF30\\View\\Raw', 'F0FViewRaw');

class_alias('JDatabaseQuery', 'F0FQueryAbstract');
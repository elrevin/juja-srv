<?php

use yii\db\Migration;

class m160302_060151_reCreateSiteUrlRules extends Migration
{
    public function up()
    {
        $filePhp = \Yii::getAlias("@app/modules/site/urlRules.php");
        if (file_exists($filePhp)) {
            unlink($filePhp);
        }
        $fileTwig = \Yii::getAlias("@app/modules/site/urlRules.twig");
        $urls = \app\modules\site\models\SiteStructure::generateSelfUrls();
        $cont = \Yii::$app->view->renderFile($fileTwig, ['urls' => $urls]);
        file_put_contents($filePhp, $cont);

    }

    public function down()
    {
        echo "m160302_060151_reCreateSiteUrlRules cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}

<?php
/**
 * The Hero Framework.
 *
 * (c) Celso Luiz de F. Fernandes  <celso@thephphero.com>
 * Date: 11.03.2019
 * Time: 13:45
 * Created by thePHPHero
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundles\PropelBundle\DependencyInjection;

use Bundles\FrameworkBundle\Interfaces\ServiceProviderInterface;
use Bundles\PropelBundle\Propel\DataCollector\PropelDataCollector;
use Monolog\Logger;
use Propel\Bundle\PropelBundle\Service\SchemaLocator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class PropelBundleServiceProvider implements ServiceProviderInterface{

    public function register(ContainerBuilder $container)
    {
        $container->setDefinition('propel.security.user.provider','%propel.security.user.provider.class%')->setPrivate(true);
        $container->setDefinition('propel.data_collector.class','%propel.data_collector.class%')->setPrivate(true);

        // Schema Locator
        $schemaLocatorDefinition = new Definition(SchemaLocator::class,[
            FileLocator::class,
            '%propel.configuration%'
        ]);
        $container->setDefinition('propel.schema_locator',$schemaLocatorDefinition );

        //Logger
        $loggerDefinition = new Definition(Logger::class,[
            //Todo: Define arguments here
        ]);
        $loggerDefinition->addTag('monolog.logger',['channel'=>'propel']);
        $container->setDefinition('propel.logger',$loggerDefinition );

        //Data Collector
        $dataCollectorDefinition = new Definition(PropelDataCollector::class,[
            new Reference('propel.logger')
        ]);
        $dataCollectorDefinition->addTag('data_collector',['template'=>'@Propel/Collector/propel', 'id'=>'propel']);
        $container->setDefinition('propel.data_collector', $dataCollectorDefinition);


    }
}

<parameters>

        <parameter key="propel.logger.class">Propel\Bundle\PropelBundle\Logger\PropelLogger</parameter>
        <parameter key="propel.twig.extension.syntax.class">Propel\Bundle\PropelBundle\Twig\Extension\SyntaxExtension</parameter>
        <parameter key="form.type_guesser.propel.class">Propel\Bundle\PropelBundle\Form\TypeGuesser</parameter>
        <parameter key="propel.form.type.model.class">Propel\Bundle\PropelBundle\Form\Type\ModelType</parameter>
        <parameter key="propel.dumper.yaml.class">Propel\Bundle\PropelBundle\DataFixtures\Dumper\YamlDataDumper</parameter>
        <parameter key="propel.loader.yaml.class">Propel\Bundle\PropelBundle\DataFixtures\Loader\YamlDataLoader</parameter>
        <parameter key="propel.loader.xml.class">Propel\Bundle\PropelBundle\DataFixtures\Loader\XmlDataLoader</parameter>
    </parameters>

    <services>






        <service id="propel.twig.extension.syntax" class="%propel.twig.extension.syntax.class%">
            <tag name="twig.extension" />
        </service>

        <service id="form.type_guesser.propel" class="%form.type_guesser.propel.class%">
            <tag name="form.type_guesser" />
        </service>

        <service id="propel.form.type.model" class="%propel.form.type.model.class%">
            <tag name="form.type" alias="model" />
        </service>

        <service id="propel.dumper.yaml" class="%propel.dumper.yaml.class%">
            <argument>%kernel.root_dir%</argument>
            <argument>%propel.configuration%</argument>
        </service>

        <service id="propel.loader.yaml" class="%propel.loader.yaml.class%">
            <argument>%kernel.root_dir%</argument>
            <argument>%propel.configuration%</argument>
            <argument type="service" id="faker.generator" on-invalid="null" />
        </service>

        <service id="propel.loader.xml" class="%propel.loader.xml.class%">
            <argument>%kernel.root_dir%</argument>
            <argument>%propel.configuration%</argument>
        </service>
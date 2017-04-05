<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2016
 *
 * @link      https://www.github.com/janhuang
 * @link      http://www.fast-d.cn/
 */

namespace FastD\Console;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ModelCreate extends Command
{
    public function configure()
    {
        $this->setName('model:create');
        $this->addArgument('name', InputArgument::REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $modelPath = app()->getPath() . '/src/Model';
        if (!file_exists($modelPath)) {
            mkdir($modelPath, 0755, true);
        }

        $name = ucfirst($input->getArgument('name'));
        $name = str_replace(['Model', 'model'], '', $name);
        $content = $this->createModelTemplate();

        $modelFile = $modelPath . '/' . $name . 'Model.php';

        if (file_exists($modelFile)) {
            throw new \LogicException(sprintf('Model %s is already exists', $name));
        }

        file_put_contents($modelFile, $content);
        $output->writeln(sprintf('Model %s created successful. path in %s', $name, $modelPath));
    }

    protected function createModelTemplate()
    {
        return <<<MODEL
<?php

namespace Model;


use FastD\Model\Model;

class UserModel extends Model
{
    const TABLE = '';
    const LIMIT = '15';

    public function select(\$page = 1)
    {
        \$offset = (\$page - 1) * static::LIMIT;
        return \$this->db->select(static::TABLE, '*', [
            'LIMIT' => [\$offset, static::LIMIT]
        ]);
    }

    public function find(\$id)
    {
        return \$this->db->get(static::TABLE, '*', [
            'OR' => [
                'id' => \$id,
            ]
        ]);
    }

    public function patch(\$id, array \$data)
    {
        \$affected = \$this->db->update(static::TABLE, \$data, [
            'OR' => [
                'id' => \$id,
            ]
        ]);

        return \$this->find(\$id);
    }

    public function create(array \$data)
    {
        \$id = \$this->db->insert(static::TABLE, \$data);

        return \$this->find(\$id);
    }

    public function deleteUser(\$id)
    {
        return \$this->db->delete(static::TABLE, [
            'id' => \$id
        ]);
    }
}
MODEL;

    }
}
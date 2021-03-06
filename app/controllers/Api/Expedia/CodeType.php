<?php
namespace App\Controllers\Api\Expedia;

use App\Validation;
use Phalcon\Validation\Validator\Regex;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use App\Models\Expedia\CodeType as CodeTypeModel;

class CodeType extends \Phalcon\Mvc\Controller
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @return mixed
     */
    public function getList()
    {
        $request  = $this->request;
        $response = $this->response;

        // validate input
        $validation = new Validation();
        $validation
            ->add(['page', 'per_page', 'parent_id'], new Regex([
                'pattern'    => '/\d+/',
                'allowEmpty' => true
            ]))
            ->validate($request->getQuery());

        $offset   = $request->get('page', 'int', 0);
        $limit    = $request->get('per_page', 'int', 10);
        $parentId = $request->get('parent_id', 'int', 0);

        list($total, $result) = CodeTypeModel::getList($offset, $limit, $parentId);

        return $response
            ->setContentType('application/json')
            ->setJsonContent([
                'total'   => $total,
                'results' => $result
            ]);
    }

    /**
     * @param $data
     */
    private function codeTypeValidate($data)
    {
        $validation = new Validation();
        $validation
            ->add('name', new PresenceOf())
            ->add('name', new StringLength([
                'max' => 50,
                'min' => 2
            ]))
            ->add('name', new Regex([
                'pattern' => '/^\S.*\S$/'
            ]))
            ->add('parent_id', new Regex([
                'pattern'    => '/\d+/',
                'allowEmpty' => true
            ]))
            ->validate($data);

        if (array_key_exists('parent_id', $data) && !CodeTypeModel::get($data['parent_id'])) {
            throw new \Exception('invalid parameter', 400);
        }

        return [
            'name'      => $data['name'],
            'parent_id' => array_key_exists('parent_id', $data) ? $data['parent_id'] : 0
        ];
    }

    /**
     * @return mixed
     */
    public function post()
    {
        $request  = $this->request;
        $response = $this->response;

        $codeType = $this->codeTypeValidate($request->getJsonRawBody(true));

        // create code-type
        $id = CodeTypeModel::create($codeType);

        if (!$id) {
            throw new \Exception('create code-type failed!');
        }

        return $response
            ->setContentType('application/json')
            ->setJsonContent(CodeTypeModel::get($id, 'master'));
    }

    /**
     * @param $id
     */
    public function checkCodeTypeId($id)
    {
        $validation = new Validation();
        $validation
            ->add('id', new Regex([
                'pattern' => '/\d+/'
            ]))
            ->validate(['id' => $id]);

        $this->data = CodeTypeModel::get($id);

        if (!$this->data) {
            throw new \Exception('not found code-type id!');
        }
    }

    /**
     * @param  $id
     * @return mixed
     */
    public function get($id)
    {
        return $this->response
            ->setContentType('application/json')
            ->setJsonContent($this->data);
    }

    /**
     * @param  $id
     * @return mixed
     */
    public function put($id)
    {
        $request  = $this->request;
        $response = $this->response;

        $inputData = $request->getJsonRawBody(true);
        $codeType  = $this->data;

        foreach ($inputData as $key => $value) {
            if (array_key_exists($key, $codeType)) {
                $codeType[$key] = $value;
            }
        }

        $codeType       = $this->codeTypeValidate($codeType);
        $codeType['id'] = $this->data['id'];
        $result         = CodeTypeModel::update($codeType);

        if (!$result) {
            throw new \Exception('update code-type failed!');
        }

        return $response
            ->setContentType('application/json')
            ->setJsonContent(CodeTypeModel::get($codeType['id'], 'master'));
    }

    /**
     * @param  $id
     * @return mixed
     */
    public function patch($id)
    {
        $request  = $this->request;
        $response = $this->response;

        $codeType       = $this->codeTypeValidate($request->getJsonRawBody(true));
        $codeType['id'] = $this->data['id'];
        $result         = CodeTypeModel::update($codeType);

        if (!$result) {
            throw new \Exception('update code-type failed!');
        }

        return $response
            ->setContentType('application/json')
            ->setJsonContent(CodeTypeModel::get($codeType['id'], 'master'));
    }

    /**
     * @param  $id
     * @return mixed
     */
    public function delete($id)
    {
        $result = CodeTypeModel::delete($this->data['id']);

        if (!$result) {
            throw new \Exception('delete code-type failed!');
        }

        return $this->response->setStatusCode(204);
    }
}

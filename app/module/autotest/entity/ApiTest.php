<?php

namespace Tymy\Module\Autotest;

abstract class ApiTest extends RequestCase
{

    abstract public function createRecord() : array;

    abstract public function mockRecord();

    /**
     * @return mixed[]
     */
    abstract protected function mockChanges(): array;

    protected function getBasePath(): string
    {
        return $this->getModule();
    }

    public function deleteRecord($recordId): void
    {
        $this->recordManager->deleteRecord($this->getBasePath(), $recordId);
    }

    //*************** COMMON TESTS, SAME FOR ALL MODULES

    public function testUnauthorized()
    {
        $this->user->logout(true);

        $this->request($this->getBasePath())->expect(401);
        $this->request($this->getBasePath(), 'POST', $this->mockRecord())->expect(401);
        $this->request($this->getBasePath() . "/1", 'PUT', $this->mockRecord())->expect(401);
        $this->request($this->getBasePath() . "/1", 'DELETE')->expect(401);
    }

    public function testMethodNotAllowed()
    {
        $this->authorizeAdmin();

        $this->request($this->getBasePath(), 'HEAD')->expect(405);

        $this->user->logout(true);
    }
    //*************** END:COMMON TESTS
}

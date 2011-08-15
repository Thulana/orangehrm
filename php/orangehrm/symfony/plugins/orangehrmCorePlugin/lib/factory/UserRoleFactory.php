<?php

class UserRoleFactory {

    private $employeeService;
    private $userEmployeeId;

    /**
     * Set Employee Data Access Object
     * @param EmployeeService $employeeService
     * @return void
     */
    public function setEmployeeService(EmployeeService $employeeService) {

        $this->employeeService = $employeeService;
    }

    /**
     * Get the Employee Data Access Object
     * @return EmployeeService
     */
    public function getEmployeeService() {

        if (is_null($this->employeeService)) {
            $this->employeeService = new EmployeeService();
        }
        return $this->employeeService;
    }

    public function getUserRoleRelatedToEmployee($userId, $employeeId) {

        if ($this->userEmployeeId == null) {
            $userRoleArray['isSupervisor'] = false;
        } else {
            if (!$this->isAdmin($userId)) {
                $userRoleArray['isSupervisor'] = $this->isSupervisorRoleRelatedToEmployee($userId, $employeeId);
            }
        }

        $userRoleArray['isAdmin'] = $this->isAdmin($userId);

        return $userRoleArray;
    }

    public function isSupervisorRoleRelatedToEmployee($userId, $employeeId) {

        $employeeService = $this->getEmployeeService();
        $employeeList = $employeeService->getSupervisorEmployeeChain($this->userEmployeeId);
        $isSupervisor = false;

        foreach ($employeeList as $employee) {

            if ($employee->getEmpNumber() == $employeeId) {
                $isSupervisor = true;
                break;
            }
        }

        return $isSupervisor;
    }

    public function isAdmin($userId) {

        return $this->getEmployeeService()->isAdmin($userId);
    }

    public function decorateUserRole($userId, $employeeId, $userEmployeeId = null) {

        $this->userEmployeeId = $userEmployeeId;
        $userObj = new User();
        $userRoleArray = $this->getUserRoleRelatedToEmployee($userId, $employeeId);

        if ($userRoleArray['isAdmin']) {
            
            return new AdminUserRoleDecorator($userObj);
        } elseif ($userRoleArray['isSupervisor']) {
            
            return new SupervisorUserRoleDecorator($userObj);
        } else {
            
            return new EssUserRoleDecorator($userObj);
        }
    }

}
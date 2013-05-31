<?php

interface sspmod_userregistration_ExtraStorage_IDriver {
	public function store(sspmod_userregistration_ExtraData_Base $data);
	public function retrieve($key, $class);
	public function delete(sspmod_userregistration_ExtraData_Base $data);
}

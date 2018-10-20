<?php

namespace System;

/**
 * Provides a mechanism for releasing unmanaged resources.
 * To browse the .NET Framework source code for this type, see the Reference Source.
 * 
 * > ``Dispose()`` 接口
*/
interface IDisposable {

    /**
     * Performs application-defined tasks associated with freeing, releasing, 
     * or resetting unmanaged resources.
    */
    public function Dispose();
}
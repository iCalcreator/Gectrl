[comment]: # (This file is part of Gectrl, PHP Genereric controller. Copyright 2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved, licence LGPLv3)

## Class Package


A transaction data information encapsulated package class with

* unique timestamp and guid (default set at instance creation)

  
* any kind of (_scalar_ / _array_ / _object_) __input__ and [ActionClassInterface] class __output__

  
* intermediate (tmp/work) data

  
* result status log

The Package class instance argument is always passed as reference when the [Gectrl] class instance are
* invoking the [ActionClassInterface] methods
* return exec result  

Package is internally using [KeyValueMgr] for workData and resultLog (below) and is to recommend as config. 


#### Class construct methods

```
__construct( [ input ] )
```
* Package constructor
* ```input``` _mixed_ 


```
init( [ input ] )
```
* ```input``` _mixed_
* Return Package class instance
* _static_
  

#### Properties && methods

**timestamp**

* valueType : _float_
* Current Unix timestamp with microseconds, default 'microtime( true)' at instance create

```
getTimestamp()
```
* Return _float_

```
setTimestamp( timestamp )
```
* Set (replace) timestamp
* ```timestamp``` _float_
* Return _static_

___

**correlationId**

* valueType : _string_
* Unique guid, default set at instance create

```
getCorrelationId()
```
* Return string


```
setCorrelationId( correlationId )
```
* Set (replace) correlationId (guid)
* ```correlationId``` string
* Return _static_

___

**input**
* valueType : _mixed_ (_scalar_ / _array_ / _object_), required

```
getInput()
```
* Return _mixed_ (_scalar_ / _array_ / _object_ passed as reference)

```
isInputSet()
```
* Return _bool_, true if input is set, otherwise false

```
setInput( input )
```
* ```input``` _mixed_ (_scalar_ / _array_ / _object_)
* Return _static_

___

**output**
* valueType : _mixed_ (_scalar_ / _array_ / _object_)

```
getOutput()
```
* Return _mixed_ (_scalar_ / _array_ / _object_ passed as reference)

```
isOutputSet()
```
* Return _bool_, true if output is set, otherwise false

```
setOutput( output )
```
* ```output``` _mixed_  (_scalar_ / _array_ / _object_)
* Return _static_

___

**workData**
* valueType : _[KeyValueMgr]_
* Opt work data, shared between actionClasses,<br>
  ex. single-load multi-use work resource(s)

```
getWorkData( [ key ] )
```
* ```key``` _string_
* Return _[KeyValueMgr]_|_mixed_|_bool_, false if key not found
* Major KeyValueMgr methods  (passed as reference)
  * KeyValueMgr::exists( key ) : _bool_
  * KeyValueMgr::get( key ) : _mixed_
  * KeyValueMgr::set( key, value ) : _[KeyValueMgr]_

```
getWorkDataKeys()
```
* Return _string[]_ workData keys

```
isWorkDataKeySet( key )
```
* ```key``` _string_
* Return _bool_, true if workData key is set, otherwise false

```
setWorkData( key, value )
```
* ```key``` _string_
* ```value``` _mixed_  (_scalar_ / _array_ / _object_)
* Return _static_

___

**resultLog**
* valueType : _[KeyValueMgr]_
* Opt (any) actionClass effect outcome

```
getResultLog( [ key ] )
```
* ```key``` _string_
* Return _[KeyValueMgr]_|_mixed_|_bool_, false if key not found
* Major KeyValueMgr methods  (passed as reference)
  * KeyValueMgr::exists( key ) : _bool_
  * KeyValueMgr::get( key ) : _mixed_
  * KeyValueMgr::set( key, value ) : _[KeyValueMgr]_

```
getResultLogKeys()
```
* Return _string[]_ resultLog keys


```
isResultLogKeySet( key )
```
* ```key``` _string_
* Return _bool_, true if resultLog key is set, otherwise false


```
setResultLog( key, value )
```
* ```key``` _string_
* ```value``` _mixed_ (_scalar_ / _array_ / _object_)
* Return _static_

---

**toString method**
```
getLoadStatus()
```
* Return _string_

---
Go to [README], [ActionClassInterface], [Gectrl] docs.

[ActionClassInterface]:ActionClassInterface.md
[Gectrl]:Gectrl.md
[KeyValueMgr]:https://github.com/iCalcreator/KeyValueMgr
[README]:../README.md

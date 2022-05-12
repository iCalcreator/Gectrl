[comment]: # (This file is part of Gectrl, PHP Genereric controller. Copyright 2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved, licence LGPLv3)

## Class Gectrl

Gectrl is a PHP generic controller class

* Supports the MVC software design pattern
* Distinguish controller and application logic using a strategy pattern

The controller provides coordination logic

The controller delegates application logic to actionClasses

* using implementations of the (strategy) [ActionClassInterface],
* invoking of actionClasses (in order) condition **evaluate** method
  * and opt (if **evaluate** returns true), logic **doAction** method,
* passing all data information in an encapsulated [Package] class instance
  * timestamp, guid, input, output


For [ActionClassInterface] example, please review **test/AcSrc/ActionExampleTest.php**.<br>
To obtain actionsClasses (FQCNs) from namespace(s), you may use [hpierce1102/ClassFinder].<br>
Any trait / interface / abstract class in FQCNs array are ignored.

#### Class construct methods

```
__construct( [ config [, logger [, actionClasses ]]] )
```
* Gectrl class constructor
* ```config``` _mixed_ (updates _[Package]_)
* ```logger``` _mixed_ (updates _[Package]_)
* ```actionClasses``` _string[]_ _[ActionClassInterface]_ FQCNs
* Creates (internal) _[Package]_ class intance (in the package property)

```
init( [ config [, logger [, actionClasses ]]] )
```
* Gectrl (static) factory method
* ```config``` _mixed_ 
* ```logger``` _mixed_
* ```actionClasses``` _string[]_ _[ActionClassInterface]_ FQCNs  
* Return _Gectrl_ class instance
* _static_


#### Class process methods

```
processOne( input )
```
* Invoke ```main``` method (below) with ONE transaction/payload, opt preloaded _[Package]_ class instance
 
```
processMany( inputs )
```
* Invoke ```main``` method (below) with array of transactions/payloads, opt preloaded _[Package]_[] class instances, one by one

```
main( [ input ] )
```
* Main method, assert Gectrl instance, invoke the actionClasses `evaluate`/`doAction` methods in order, using _[Package]_, _config_ and _logger_ as arguments 
* ```input``` 
  * mixed (_scalar_/_array_/_object_)<br>
  stores the input in the default (internally) created _[Package]_ class instance
  * _[Package]_  (passed as reference)<br>
  a externally created (preloaded) _[Package]_ class instance
* Return _[Package]_
* Throws _InvalidArgumentException_, _RuntimeException_


#### Properties && methods

FQCN =  Fully Qualified Class Name

**actionClasses**

* valueType : _string[]_ actionClass FQCNs

```
getActionClasses()
```
* Return _string[]_ actionClasses (FQCNs)

```
isActionClassSet( [ fqcn] )
```
* ```fqcn``` _string_ _[ActionClassInterface]_ FQCN
* Return _bool_, true if actionsClasses (opt spec fqcn) is set, otherwise false

```
addActionClass( actionClass )

```
* ```actionClass``` _string_ _[ActionClassInterface]_ FQCN<br>
  the ```actionClass``` class MUST implement _[ActionClassInterface]_<br>
  no action on traits / interfaces / abstract classes
* Return _static_
* Throws _InvalidArgumentException_ on other class, interface or trait error

```
setActionClasses( actionClasses )
```
* Set (string[]) actionClasses (FQCNs)
* any trait / interface / abstract class in FQCNs array ignored
* ```actionClasses``` _string[]_ _[ActionClassInterface]_ FQCNs<br>
* Return _static_
* Throws _InvalidArgumentException_ on other class, interface or trait error

**package**

* valueType : _[Package]_

```
getPackage()
```
* Return _[Package]_ (passed as reference), null if not set

```
setPackage( package )
```
* Set _[Package]_
* ```package``` _[Package]_  (passed as reference)
* Throws _InvalidArgumentException_ on empty _[Package]_ input
* Return _static_


**config**

* valueType : _mixed_

```
getConfig()
```
* Return _mixed_, null if not set

```
isConfigSet()
```
* Return _bool_, true if config is set, otherwise false


```
setConfig( config )
```
* Set config
* ```config``` _mixed_
* Return _static_


**logger**

* valueType : _mixed_

```
getLogger()
```
* Return _mixed_, null if not set

```
isLoggerSet()
```
* Return _bool_, true if logger is set, otherwise false


```
setlogger( logger )
```
* Set logger
* ```logger``` _mixed_
* Return _static_

---
Go to [README], [ActionClassInterface], [Package] docs.

[ActionClassInterface]:ActionClassInterface.md
[hpierce1102/ClassFinder]:https://gitlab.com/hpierce1102/ClassFinder
[Package]:Package.md
[README]:../README.md

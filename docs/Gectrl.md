[comment]: # (This file is part of Gectrl, PHP Genereric controller. Copyright 2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved, licence LGPLv3)

## Class Gectrl

Gectrl is a PHP generic controller class

* Supports the MVC software design pattern
* Distinguish controller and application logic using a strategy pattern

The controller provides coordination logic

The controller delegates application logic to actionClasses

* using implementations of the (strategy) [ActionClassInterface],
* invoking of actionClass condition **evaluate** (in order)
  * and opt, logic **doAction** methods,
* passing all data information in an encapsulated [Package] class instance
  * input, output, config, logger etc


For [ActionClassInterface] example, please review **test/AcSrc/ActionExampleTest.php**.<br>
To obtain actionsClasses (FQCNs) from namespace(s), you may use [hpierce1102/ClassFinder].<br>
Any trait / interface / abstract class in FQCNs array are ignored.

#### Class common methods

```
__construct( [ config [, logger [, actionClasses ]]] )
```
* Gectrl constructor
* ```config``` _mixed_ (updates _[Package]_)
* ```logger``` _mixed_ (updates _[Package]_)
* ```actionClasses``` _string[]_ _[ActionClassInterface]_ FQCNs
* Creates (internal) _[Package]_ class intance (in the package property)

```
init( [ config [, logger [, actionClasses ]]] )
```
* Gectrl (static) factory
* ```config``` _mixed_ (updates _[Package]_) 
* ```logger``` _mixed_ (updates _[Package]_)
* ```actionClasses``` _string[]_ _[ActionClassInterface]_ FQCNs  
* Return _Gectrl_ class instance
* _static_

---

```
main( [ input ] )
```
* Main method, assert Gectrl instance, invoke the actionClasses 'evaluate'/'doAction' methods in order
* ```input``` mixed (_scalar_/_array_/_object_)<br>
 stores the input in the default (internally) created _[Package]_ class instance
* ```input``` _[Package]_  (passed as reference)<br>
 a (replacing) externally created (preloaded) _[Package]_ class instance
* Return _[Package]_
* Throws _RuntimeException_


#### Properties && methods

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
* Return _bool_, true if actionsClasses (fqcn) is set, otherwise false

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
* Created at Gectrl class intance creation


```
getPackage()
```
* Return _[Package]_ (passed as reference)

```
setPackage( package )
```
* Set (replacing) _[Package]_
* ```package``` _[Package]_  (passed as reference)
* Return _static_

---
Go to [README], [ActionClassInterface], [Package] docs.

[ActionClassInterface]:ActionClassInterface.md
[hpierce1102/ClassFinder]:https://gitlab.com/hpierce1102/ClassFinder
[Package]:Package.md
[README]:../README.md

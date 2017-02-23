// 程序主入口
package main

import (
	"encoding/json"
	"fmt"
	"io/ioutil"
	"time"
)

// Province 省份
type Province struct {
	Code string
	Name string
}

// City 城市
type City struct {
	Code       string
	Name       string
	ParentCode string `json:"parent_code"`
}

// Area 地区
type Area struct {
	Code       string
	Name       string
	ParentCode string `json:"parent_code"`
}

func main() {
	start := time.Now()
	provinces := []Province{}
	cities := []City{}
	areas := []Area{}
	err := FileJSON2StructArray("../dist/provinces.json", &provinces)
	if err != nil {
		fmt.Println(err.Error())
		return
	}

	err = FileJSON2StructArray("../dist/cities.json", &cities)
	if err != nil {
		fmt.Println(err.Error())
		return
	}

	err = FileJSON2StructArray("../dist/areas.json", &areas)
	if err != nil {
		fmt.Println(err.Error())
		return
	}
	elapsed1 := time.Since(start)
	fmt.Printf("%+v", provinces)
	fmt.Printf("%+v", cities)
	fmt.Printf("%+v", areas)
	elapsed2 := time.Since(start)
	fmt.Println()
	fmt.Println("读取文件并转换耗时：", elapsed1)
	fmt.Println("总运行耗时：", elapsed2)
}

// ReadFromFile 从文件中读取数据
func ReadFromFile(filename string) ([]byte, error) {
	bytes, err := ioutil.ReadFile(filename)
	if err != nil {
		return nil, fmt.Errorf("读取文件时出错：%s", err.Error())
	}
	return bytes, nil
}

// JSON2StructArray JSON数据转为对象数据
func JSON2StructArray(jsonData []byte, structArray interface{}) error {
	err := json.Unmarshal(jsonData, &structArray)
	if err != nil {
		return fmt.Errorf("JSON转对象数据出错：%s", err.Error())
	}
	return nil
}

// FileJSON2StructArray 从保存JSON数据的文件中读取对象数据
func FileJSON2StructArray(filename string, structArray interface{}) error {
	bytes, err := ReadFromFile(filename)
	if err != nil {
		return err
	}
	err = JSON2StructArray(bytes, &structArray)
	if err != nil {
		return err
	}
	return nil
}

import fnmatch
import os

def replacePhpApp():
    header = [
        '<?php\n',
        '\n',
        '/**\n',
        ' * Copyright 2017-2021 IQRF Tech s.r.o.\n',
        ' * Copyright 2019-2021 MICRORISC s.r.o.\n',
        ' *\n',
        ' * Licensed under the Apache License, Version 2.0 (the "License");\n',
        ' * you may not use this file except in compliance with the License.\n',
        ' * You may obtain a copy of the License at\n',
        ' *\n',
        ' *     http://www.apache.org/licenses/LICENSE-2.0\n',
        ' *\n',
        ' * Unless required by applicable law or agreed to in writing, software\n',
        ' * distributed under the License is distributed on an "AS IS" BASIS,\n',
        ' * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.\n',
        ' * See the License for the specific language governing permissions and\n',
        ' * limitations under the License.\n',
        ' */\n'
    ]

    matches = []

    for root, dirnames, filenames in os.walk('app'):
        for filename in fnmatch.filter(filenames, '*.php'):
            matches.append(os.path.join(root, filename))

    for match in matches:
        file = open(match, 'r')
        lines = file.readlines()
        file.close()

        #print(''.join(lines))

        needle = -1

        for i in range(len(lines)):
            if lines[i].strip() == "declare(strict_types = 1);":
                needle = i
                break
        
        if needle == -1:
            continue

        lines = lines[needle:]
        lines = header + lines
        
        #print(''.join(lines))
        #break
        file = open(match, 'w')
        file.write(''.join(lines))
        file.close()

def insertPhpTests():
    header = [
        '/**\n',
        ' * Copyright 2017-2021 IQRF Tech s.r.o.\n',
        ' * Copyright 2019-2021 MICRORISC s.r.o.\n',
        ' *\n',
        ' * Licensed under the Apache License, Version 2.0 (the "License");\n',
        ' * you may not use this file except in compliance with the License.\n',
        ' * You may obtain a copy of the License at\n',
        ' *\n',
        ' *     http://www.apache.org/licenses/LICENSE-2.0\n',
        ' *\n',
        ' * Unless required by applicable law or agreed to in writing, software\n',
        ' * distributed under the License is distributed on an "AS IS" BASIS,\n',
        ' * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.\n',
        ' * See the License for the specific language governing permissions and\n',
        ' * limitations under the License.\n',
        ' */'
    ]

    matches = []

    for root, dirnames, filenames in os.walk('tests'):
        for filename in fnmatch.filter(filenames, '*.php'):
            matches.append(os.path.join(root, filename))
    
    for match in matches:
        file = open(match, 'r')
        lines = file.readlines()
        file.close()
        
        #print(''.join(lines))
        
        lines = lines[0:2] + header + lines[1:]

        #print(''.join(lines))
        #break
        file = open(match, 'w')
        file.write(''.join(lines))
        file.close()
        

def insertToJsTs():
    header = [
        '/**\n',
        ' * Copyright 2017-2021 IQRF Tech s.r.o.\n',
        ' * Copyright 2019-2021 MICRORISC s.r.o.\n',
        ' *\n',
        ' * Licensed under the Apache License, Version 2.0 (the "License");\n',
        ' * you may not use this file except in compliance with the License.\n',
        ' * You may obtain a copy of the License at\n',
        ' *\n',
        ' *     http://www.apache.org/licenses/LICENSE-2.0\n',
        ' *\n',
        ' * Unless required by applicable law or agreed to in writing, software\n',
        ' * distributed under the License is distributed on an "AS IS" BASIS,\n',
        ' * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.\n',
        ' * See the License for the specific language governing permissions and\n',
        ' * limitations under the License.\n',
        ' */\n'
    ]

    matches = []

    for root, dirnames, filenames in os.walk('src'):
        for filename in fnmatch.filter(filenames, '*.js'):
            matches.append(os.path.join(root, filename))
        for filename in fnmatch.filter(filenames, '*.ts'):
            matches.append(os.path.join(root, filename))

    for match in matches:
        file = open(match, 'r')
        lines = file.readlines()
        file.close()

        print(''.join(lines))

        lines = header + lines

        #print(''.join(lines))
        #break

        file = open(match, 'w')
        file.write(''.join(lines))
        file.close()

def insertToVue():
    header = [
        '<!--\n',
        'Copyright 2017-2021 IQRF Tech s.r.o.\n',
        'Copyright 2019-2021 MICRORISC s.r.o.\n',
        '\n',
        'Licensed under the Apache License, Version 2.0 (the "License");\n',
        'you may not use this file except in compliance with the License.\n',
        'You may obtain a copy of the License at\n',
        '\n',
        '    http://www.apache.org/licenses/LICENSE-2.0\n',
        '\n',
        'Unless required by applicable law or agreed to in writing, software,\n',
        'distributed under the License is distributed on an "AS IS" BASIS,\n',
        'WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied\n',
        'See the License for the specific language governing permissions and\n',
        'limitations under the License.\n',
        '-->\n'
    ]
    matches = []

    for root, dirnames, filenames in os.walk('src'):
        for filename in fnmatch.filter(filenames, '*.vue'):
            matches.append(os.path.join(root, filename))

    for match in matches:
        file = open(match, 'r')
        lines = file.readlines()
        file.close()

        #print(''.join(lines))

        lines = header + lines
        
        #print(''.join(lines))
        #break
        file = open(match, 'w')
        file.write(''.join(lines))
        file.close()
    
def main():
    replacePhpApp()
    insertPhpTests()
    insertToJsTs()
    insertToVue()

if __name__ == '__main__':
    main()
